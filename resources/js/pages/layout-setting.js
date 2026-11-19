    /* =========================================================
     * DATA DARI SERVER
     * ========================================================= */
    const sectionMetadata = window.layoutSettingConfig?.sectionMeta || {};
    const rolePermissionsMap = window.layoutSettingConfig?.rolePermissionsMap || {};
    const factoryDefaultCardOrder = window.layoutSettingConfig?.defaultCardOrdersMap || {};
    // layoutStateMap = { all: {sections:[], cards:{}}, "<Role>": {sections:[], cards:{}} }
    // 'all' = layout global. Tiap role = layout global + override khusus role itu.
    let layoutStateMap = window.layoutSettingConfig?.layoutStateMap || {};

    /* =========================================================
     * STATE
     * ========================================================= */
    let sortableGridEl = null;
    let sortableTableBodyEl = null;
    let gridSortableInstance = null;
    let tableSortableInstance = null;
    let cardSortableInstances = {};
    let initialOrder = [];
    let initialCardOrder = {};
    let isDirty = false;
    let historyStack = [];
    let historyIndex = -1;
    let isApplyingHistory = false;
    let currentRoleFilter = 'all';

    function byId(id) {
        return document.getElementById(id);
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    /* =========================================================
     * SWITCH TAMPILAN (Grid / Tabel / Detail Card)
     * ========================================================= */
    function switchSectionDisplay(subMode) {
        const views = {
            grid: byId('section-subview-grid'),
            table: byId('table-view-container'),
            cards: byId('cards-view-container')
        };
        const btns = {
            grid: byId('btn-subview-grid'),
            table: byId('btn-subview-table'),
            cards: byId('btn-subview-cards')
        };

        Object.keys(views).forEach(key => {
            if (views[key]) views[key].classList.toggle('d-none', key !== subMode);
            if (btns[key]) {
                btns[key].classList.toggle('active', key === subMode);
                btns[key].classList.toggle('text-secondary', key !== subMode);
            }
        });

        if (subMode === 'cards') {
            updateCardsRoleFilterView(currentRoleFilter);
        }
    }

    /* =========================================================
     * SNAPSHOT / HISTORY (UNDO - REDO)
     * ========================================================= */
    function getSnapshot() {
        const sections = sortableGridEl
            ? Array.from(sortableGridEl.querySelectorAll('.sortable-item')).map(el => el.dataset.id)
            : [];
        const cards = {};
        document.querySelectorAll('.card-sortable-list').forEach(listEl => {
            const secKey = listEl.dataset.section;
            if (secKey) {
                cards[secKey] = Array.from(listEl.querySelectorAll('.card-sortable-item')).map(el => el.dataset.id);
            }
        });
        return { sections, cards };
    }

    function updateHistoryButtons() {
        const undoBtn = byId('btn-undo-action');
        const redoBtn = byId('btn-redo-action');
        if (undoBtn) undoBtn.disabled = (historyIndex <= 0);
        if (redoBtn) redoBtn.disabled = (historyIndex >= historyStack.length - 1);
    }

    function recordHistory() {
        if (isApplyingHistory) return;
        const snapshot = getSnapshot();
        const currentSnap = historyStack[historyIndex];
        if (currentSnap && JSON.stringify(currentSnap) === JSON.stringify(snapshot)) {
            return;
        }
        if (historyIndex < historyStack.length - 1) {
            historyStack = historyStack.slice(0, historyIndex + 1);
        }
        historyStack.push(snapshot);
        historyIndex = historyStack.length - 1;
        updateHistoryButtons();
    }

    function applySnapshot(snapshot) {
        if (!snapshot) return;
        isApplyingHistory = true;

        try {
            if (snapshot.sections && snapshot.sections.length > 0) {
                snapshot.sections.forEach(id => {
                    if (sortableGridEl) {
                        const item = sortableGridEl.querySelector(`.sortable-item[data-id="${id}"]`);
                        if (item) sortableGridEl.appendChild(item);
                    }
                    if (sortableTableBodyEl) {
                        const row = sortableTableBodyEl.querySelector(`.table-sortable-row[data-id="${id}"]`);
                        if (row) sortableTableBodyEl.appendChild(row);
                    }
                });
                updateAllBadgesAndSelects();
            }

            if (snapshot.cards) {
                Object.keys(snapshot.cards).forEach(secKey => {
                    const listEl = document.querySelector(`.card-sortable-list[data-section="${secKey}"]`);
                    if (listEl) {
                        snapshot.cards[secKey].forEach(cardId => {
                            const cardItem = listEl.querySelector(`.card-sortable-item[data-id="${cardId}"]`);
                            if (cardItem) listEl.appendChild(cardItem);
                        });
                        updateCardBadgesForSection(secKey);
                    }
                });
            }
        } finally {
            isApplyingHistory = false;
        }

        checkDirtyState();
        updateHistoryButtons();
    }

    function undo() {
        if (historyIndex > 0) {
            historyIndex--;
            applySnapshot(historyStack[historyIndex]);
        }
    }

    function redo() {
        if (historyIndex < historyStack.length - 1) {
            historyIndex++;
            applySnapshot(historyStack[historyIndex]);
        }
    }

    /* =========================================================
     * KARTU: BADGE, PINDAH POSISI
     * ========================================================= */
    function updateCardBadgesForSection(sectionKey) {
        const container = document.querySelector(`.card-sortable-list[data-section="${sectionKey}"]`);
        if (!container) return;

        container.querySelectorAll('.card-sortable-item').forEach((item, index) => {
            const pos = index + 1;
            const select = item.querySelector('.card-position-select');
            if (select) select.value = pos;
            const badge = item.querySelector('.card-order-badge');
            if (badge) badge.textContent = '#' + pos;
        });
    }

    function moveCardToIndex(sectionKey, cardId, targetIndex1Based) {
        const listEl = document.querySelector(`.card-sortable-list[data-section="${sectionKey}"]`);
        if (!listEl) return;

        const cards = Array.from(listEl.querySelectorAll('.card-sortable-item'));
        const currentCard = listEl.querySelector(`.card-sortable-item[data-id="${cardId}"]`);
        if (!currentCard) return;

        const currentIndex = cards.indexOf(currentCard);
        const targetIndex = Math.max(0, Math.min(cards.length - 1, targetIndex1Based - 1));
        if (currentIndex === -1 || currentIndex === targetIndex) return;

        if (targetIndex >= cards.length - 1) {
            listEl.appendChild(currentCard);
        } else if (targetIndex > currentIndex) {
            listEl.insertBefore(currentCard, cards[targetIndex].nextSibling);
        } else {
            listEl.insertBefore(currentCard, cards[targetIndex]);
        }

        updateCardBadgesForSection(sectionKey);
        checkDirtyState();
        recordHistory();
    }

    /* =========================================================
     * SECTION: SINKRON GRID <-> TABEL, BADGE, PINDAH POSISI
     * ========================================================= */
    function syncFromGridToTable() {
        if (!sortableGridEl || !sortableTableBodyEl) return;
        Array.from(sortableGridEl.querySelectorAll('.sortable-item')).map(el => el.dataset.id).forEach(id => {
            const row = sortableTableBodyEl.querySelector(`.table-sortable-row[data-id="${id}"]`);
            if (row) sortableTableBodyEl.appendChild(row);
        });
    }

    function syncFromTableToGrid() {
        if (!sortableGridEl || !sortableTableBodyEl) return;
        Array.from(sortableTableBodyEl.querySelectorAll('.table-sortable-row')).map(el => el.dataset.id).forEach(id => {
            const item = sortableGridEl.querySelector(`.sortable-item[data-id="${id}"]`);
            if (item) sortableGridEl.appendChild(item);
        });
    }

    function buildPositionOptions(total, pos) {
        let html = '';
        for (let i = 1; i <= total; i++) {
            html += `<option value="${i}" ${i === pos ? 'selected' : ''}>Urutan #${i}</option>`;
        }
        return html;
    }

    function updateAllBadgesAndSelects() {
        if (!sortableGridEl || !sortableTableBodyEl) return;

        // 1. Grid (hanya item yang tampil)
        const visibleGridItems = Array.from(sortableGridEl.querySelectorAll('.sortable-item:not(.d-none)'));
        const totalVisible = visibleGridItems.length;

        visibleGridItems.forEach((item, index) => {
            const pos = index + 1;
            const isLeft = (pos % 2 !== 0);
            const targetColor = isLeft ? '#2563eb' : '#059669';
            const card = item.querySelector('.section-card');

            if (card) {
                card.style.setProperty('border', `2px solid ${targetColor}`, 'important');
                const accentStrip = card.querySelector('.card-accent-strip');
                if (accentStrip) accentStrip.style.backgroundColor = targetColor;
            }

            const badge = item.querySelector('.order-badge');
            if (badge) {
                badge.textContent = '#' + pos;
                badge.style.setProperty('background-color', targetColor, 'important');
            }

            const colIndicator = item.querySelector('.column-indicator');
            if (colIndicator) {
                colIndicator.textContent = isLeft ? 'KIRI' : 'KANAN';
                colIndicator.className = 'column-indicator badge rounded-pill px-1.5 py-0.5 mt-1 d-block border ' +
                    (isLeft ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-success-subtle text-success border-success-subtle');
            }

            const select = item.querySelector('.position-select');
            if (select) {
                select.innerHTML = buildPositionOptions(totalVisible, pos);
                select.value = pos;
            }
        });

        // 2. Tabel (hanya baris yang tampil)
        const visibleTableRows = Array.from(sortableTableBodyEl.querySelectorAll('.table-sortable-row:not(.d-none)'));
        visibleTableRows.forEach((row, index) => {
            const pos = index + 1;
            const isLeft = (pos % 2 !== 0);
            const targetColor = isLeft ? '#2563eb' : '#059669';

            const badge = row.querySelector('.order-badge');
            if (badge) {
                badge.textContent = '#' + pos;
                badge.style.setProperty('background-color', targetColor, 'important');
            }

            const colIndicator = row.querySelector('.column-indicator');
            if (colIndicator) {
                colIndicator.textContent = isLeft ? 'Kolom Kiri' : 'Kolom Kanan';
                colIndicator.className = 'column-indicator badge rounded-pill px-2 py-1 border ' +
                    (isLeft ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-success-subtle text-success border-success-subtle');
            }

            const select = row.querySelector('.position-select');
            if (select) {
                select.innerHTML = buildPositionOptions(totalVisible, pos);
                select.value = pos;
            }
        });

        // 3. Detail Card
        syncFromGridToCards();
    }

    function moveItemToIndex(itemId, targetVisibleIndex1Based) {
        if (!sortableGridEl || !sortableTableBodyEl) return;

        const visibleGridItems = Array.from(sortableGridEl.querySelectorAll('.sortable-item:not(.d-none)'));
        const currentItem = sortableGridEl.querySelector(`.sortable-item[data-id="${itemId}"]`);
        if (!currentItem) return;

        const currentVisibleIdx = visibleGridItems.indexOf(currentItem);
        const targetIdx = Math.max(0, Math.min(visibleGridItems.length - 1, targetVisibleIndex1Based - 1));
        if (currentVisibleIdx === -1 || currentVisibleIdx === targetIdx) return;

        const referenceItem = visibleGridItems[targetIdx];
        if (targetIdx > currentVisibleIdx) {
            if (referenceItem.nextSibling) {
                sortableGridEl.insertBefore(currentItem, referenceItem.nextSibling);
            } else {
                sortableGridEl.appendChild(currentItem);
            }
        } else {
            sortableGridEl.insertBefore(currentItem, referenceItem);
        }

        syncFromGridToTable();
        updateAllBadgesAndSelects();
        checkDirtyState();
        recordHistory();
    }

    /* =========================================================
     * SINKRON GRID -> DETAIL CARD
     * ========================================================= */
    function syncFromGridToCards() {
        const grid = byId('sortable-list');
        const cardsContainer = byId('cards-sections-container');
        if (!grid || !cardsContainer) return;

        const wrapperMap = {};
        cardsContainer.querySelectorAll('.dashboard-section-wrapper').forEach(w => {
            if (w.dataset.section) wrapperMap[w.dataset.section] = w;
        });
        const totalCount = Object.keys(wrapperMap).length || 11;

        Array.from(grid.querySelectorAll('.sortable-item')).forEach(item => {
            const wrapper = wrapperMap[item.dataset.id];
            if (!wrapper) return;
            cardsContainer.appendChild(wrapper);
            wrapper.classList.toggle('d-none', item.classList.contains('d-none'));
        });

        const visibleWrappers = Array.from(cardsContainer.querySelectorAll('.dashboard-section-wrapper:not(.d-none)'));
        visibleWrappers.forEach((wrapper, index) => {
            const pos = index + 1;
            const isLeft = (pos % 2 !== 0);
            const accentColor = isLeft ? '#2563eb' : '#059669';

            const box = wrapper.querySelector('.dashboard-section-box');
            if (box) box.style.setProperty('border', `2px solid ${accentColor}`, 'important');

            const badge = wrapper.querySelector('.section-pos-badge');
            if (badge) {
                badge.textContent = `Section #${pos} (${isLeft ? 'Kiri' : 'Kanan'})`;
                badge.style.setProperty('background-color', accentColor, 'important');
            }
        });

        const visibleCount = visibleWrappers.length;
        const countBadgeEl = byId('cards-section-count-badge');
        if (countBadgeEl) {
            countBadgeEl.textContent = currentRoleFilter === 'all'
                ? `Menampilkan ${totalCount}/${totalCount} Section`
                : `Menampilkan ${visibleCount}/${totalCount} Section (${currentRoleFilter})`;
        }

        const emptyStateEl = byId('cards-empty-state');
        if (emptyStateEl) {
            if (visibleCount === 0) {
                emptyStateEl.classList.remove('d-none');
                const emptyText = emptyStateEl.querySelector('.empty-role-name');
                if (emptyText) emptyText.textContent = currentRoleFilter;
            } else {
                emptyStateEl.classList.add('d-none');
            }
        }
    }

    function updateCardsRoleFilterView(role) {
        const roleNameEl = byId('cards-role-name');
        if (roleNameEl) {
            roleNameEl.textContent = (role === 'all') ? 'Semua Role (Global)' : role;
        }
        syncFromGridToCards();
    }

    /* =========================================================
     * TERAPKAN LAYOUT (GLOBAL / ROLE) KE DOM
     * ========================================================= */
    function applyLayoutState(state) {
        if (!state || !sortableGridEl) return;

        isApplyingHistory = true;
        try {
            // Urutan section (grid + tabel)
            (state.sections || []).forEach(id => {
                const item = sortableGridEl.querySelector(`.sortable-item[data-id="${id}"]`);
                if (item) sortableGridEl.appendChild(item);
                if (sortableTableBodyEl) {
                    const row = sortableTableBodyEl.querySelector(`.table-sortable-row[data-id="${id}"]`);
                    if (row) sortableTableBodyEl.appendChild(row);
                }
            });

            // Urutan card per section (null = default bawaan)
            Object.keys(factoryDefaultCardOrder).forEach(secKey => {
                const listEl = document.querySelector(`.card-sortable-list[data-section="${secKey}"]`);
                if (!listEl) return;

                const factory = factoryDefaultCardOrder[secKey] || [];
                const saved = (state.cards && Array.isArray(state.cards[secKey])) ? state.cards[secKey] : [];
                const fullOrder = [
                    ...saved.filter(id => factory.includes(id)),
                    ...factory.filter(id => !saved.includes(id))
                ];

                fullOrder.forEach(cId => {
                    const item = listEl.querySelector(`.card-sortable-item[data-id="${cId}"]`);
                    if (item) listEl.appendChild(item);
                });
                updateCardBadgesForSection(secKey);
            });
        } finally {
            isApplyingHistory = false;
        }

        updateAllBadgesAndSelects();

        // State yang baru dimuat dianggap "sudah tersimpan" -> jadikan baseline
        const snap = getSnapshot();
        initialOrder = [...snap.sections];
        initialCardOrder = JSON.parse(JSON.stringify(snap.cards));
        historyStack = [snap];
        historyIndex = 0;
        checkDirtyState();
    }

    function applyRoleLayoutState(role) {
        const state = layoutStateMap[role] || layoutStateMap['all'];
        if (state) applyLayoutState(state);
        updateAllBadgesAndSelects();
    }

    /* =========================================================
     * FILTER ROLE
     * ========================================================= */
    function handleRoleFilterChange(role) {
        if (isDirty) {
            Swal.fire({
                icon: 'warning',
                title: 'Ada perubahan belum disimpan',
                text: 'Pindah filter role akan membuang perubahan tersebut.',
                showCancelButton: true,
                confirmButtonText: 'Buang & Pindah',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    doRoleFilterChange(role);
                } else {
                    const sel = byId('role-filter-select');
                    if (sel) sel.value = currentRoleFilter;
                }
            });
            return;
        }
        doRoleFilterChange(role);
    }

    function doRoleFilterChange(role) {
        currentRoleFilter = role;
        const isAll = (role === 'all');

        const gridItems = document.querySelectorAll('#sortable-list .sortable-item');
        const tableRows = document.querySelectorAll('#table-sortable-body .table-sortable-row');
        const allowedMap = isAll ? null : (rolePermissionsMap[role] || {});
        const totalCount = gridItems.length || 11;
        let visibleCount = 0;

        gridItems.forEach(el => {
            const show = isAll || !!allowedMap[el.dataset.id];
            el.classList.toggle('d-none', !show);
            if (show) visibleCount++;
        });
        tableRows.forEach(el => {
            const show = isAll || !!allowedMap[el.dataset.id];
            el.classList.toggle('d-none', !show);
        });

        const filterAlert = byId('role-filter-alert');
        const selectedRoleNameEl = byId('selected-role-name');
        const counterBadge = byId('role-filter-counter-badge');
        const headerResetText = byId('header-reset-btn-text');

        if (isAll) {
            if (filterAlert) filterAlert.classList.add('d-none');
            if (headerResetText) headerResetText.textContent = 'Reset Default';
            if (counterBadge) {
                counterBadge.className = 'badge bg-light text-secondary border rounded-pill px-3 py-2 small fw-semibold';
                counterBadge.innerHTML = `<i class="fa-solid fa-layer-group text-primary me-1"></i> Menampilkan <span id="visible-sections-count">${totalCount}</span>/${totalCount} Section (Global)`;
            }
        } else {
            if (filterAlert) filterAlert.classList.remove('d-none');
            if (selectedRoleNameEl) selectedRoleNameEl.textContent = role;
            if (headerResetText) headerResetText.textContent = 'Reset Role Ini';
            if (counterBadge) {
                counterBadge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 small fw-bold';
                counterBadge.innerHTML = `<i class="fa-solid fa-filter me-1"></i> Menampilkan <span id="visible-sections-count">${visibleCount}</span>/${totalCount} Section (${role})`;
            }
        }

        // Muat urutan section & card milik role ini (atau global jika 'all')
        applyRoleLayoutState(role);
        updateCardsRoleFilterView(role);
    }

    function resetRoleFilter() {
        const selectEl = byId('role-filter-select');
        if (selectEl) selectEl.value = 'all';
        handleRoleFilterChange('all');
    }

    /* =========================================================
     * RESET (GLOBAL & PER ROLE)
     *  - role 'all'  -> hanya tabel global (dashboard_layouts)
     *  - role tertentu -> hanya override role itu (dashboard_role_layouts)
     * ========================================================= */
    function runReset(type, role) {
        const isRole = !!role && role !== 'all';
        const label = (type === 'sections') ? 'Urutan Section' : 'Urutan Card';

        Swal.fire({
            title: `Reset ${label}${isRole ? ` untuk Role: ${role}` : ' (Global)'}?`,
            text: isRole
                ? `Hanya role "${role}" yang dikembalikan ke default global. Role lain dan pengaturan global tidak terpengaruh.`
                : 'Pengaturan global dikembalikan ke default. Role yang punya pengaturan sendiri tidak ikut berubah.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reset',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Mereset...',
                text: 'Mengembalikan ke posisi default.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(window.layoutSettingConfig?.urls?.reset || '/admin/layout-setting/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ type: type, role: isRole ? role : 'all' })
            })
            .then(r => r.ok ? r.json() : r.json().then(e => { throw e; }))
            .then(data => {
                if (data.layout_state) layoutStateMap = data.layout_state;
                applyRoleLayoutState(isRole ? role : 'all');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil di-Reset!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            })
            .catch(error => {
                console.error('Error reset layout:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: (error && error.message) || 'Terjadi kesalahan saat mereset.'
                });
            });
        });
    }

    // Reset khusus role yang sedang difilter (pilih: section atau card)
    function executeResetDefault() {
        if (currentRoleFilter === 'all') {
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: 'Anda sedang di mode Global. Gunakan tombol "Reset Default" di header untuk reset global.'
            });
            return;
        }

        const roleToReset = currentRoleFilter;

        Swal.fire({
            title: `Reset Default untuk Role: ${roleToReset}`,
            html: `<p class="text-muted small mb-3">Pilih yang ingin di-reset khusus untuk role <strong class="text-primary">${roleToReset}</strong>:</p>
                   <div class="d-grid gap-2">
                     <button type="button" id="swal-reset-section-btn" class="btn btn-outline-warning rounded-pill fw-semibold py-2">
                       <i class="fa-solid fa-layer-group me-2"></i> Reset Urutan Section (role ini saja)
                     </button>
                     <button type="button" id="swal-reset-cards-btn" class="btn btn-outline-primary rounded-pill fw-semibold py-2">
                       <i class="fa-solid fa-id-card me-2"></i> Reset Urutan Card (role ini saja)
                     </button>
                   </div>`,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Batal',
            cancelButtonColor: '#6c757d',
            didOpen: () => {
                byId('swal-reset-section-btn').addEventListener('click', () => {
                    Swal.close();
                    setTimeout(() => runReset('sections', roleToReset), 200);
                });
                byId('swal-reset-cards-btn').addEventListener('click', () => {
                    Swal.close();
                    setTimeout(() => runReset('cards', roleToReset), 200);
                });
            }
        });
    }

    /* =========================================================
     * DIRTY STATE (perubahan belum disimpan)
     * ========================================================= */
    function checkDirtyState() {
        let sectionDirty = false;
        let cardsDirty = false;

        if (sortableGridEl) {
            const currentOrder = Array.from(sortableGridEl.querySelectorAll('.sortable-item')).map(el => el.dataset.id);
            sectionDirty = JSON.stringify(currentOrder) !== JSON.stringify(initialOrder);
        }

        document.querySelectorAll('.card-sortable-list').forEach(listEl => {
            const secKey = listEl.dataset.section;
            const currentCards = Array.from(listEl.querySelectorAll('.card-sortable-item')).map(el => el.dataset.id);
            if (initialCardOrder[secKey] && JSON.stringify(currentCards) !== JSON.stringify(initialCardOrder[secKey])) {
                cardsDirty = true;
            }
        });

        isDirty = sectionDirty || cardsDirty;
        const hasRedo = (historyIndex < historyStack.length - 1);

        const stickySaveBar = byId('sticky-save-bar');
        const stickyTitle = byId('sticky-title');
        const stickyDesc = byId('sticky-desc');
        const stickyPulse = byId('sticky-pulse-dot');
        const saveBtns = document.querySelectorAll('.btn-save-action');
        const saveBtnSticky = stickySaveBar ? stickySaveBar.querySelector('.btn-save-action') : null;

        if (isDirty) {
            saveBtns.forEach(btn => {
                btn.classList.add('dirty');
                btn.disabled = false;
            });
            if (stickyTitle) stickyTitle.textContent = 'Urutan Berubah';
            if (stickyDesc) stickyDesc.textContent = currentRoleFilter === 'all'
                ? 'Klik Simpan untuk menerapkan tata letak global.'
                : `Klik Simpan untuk menerapkan tata letak role "${currentRoleFilter}".`;
            if (stickyPulse) stickyPulse.style.backgroundColor = '#f59e0b';
            if (stickySaveBar) stickySaveBar.classList.add('show');
        } else {
            saveBtns.forEach(btn => btn.classList.remove('dirty'));

            if (hasRedo) {
                if (stickyTitle) stickyTitle.textContent = 'Kembali ke Posisi Awal';
                if (stickyDesc) stickyDesc.textContent = 'Klik → untuk memajukan perubahan kembali.';
                if (stickyPulse) stickyPulse.style.backgroundColor = '#3b82f6';
                if (saveBtnSticky) saveBtnSticky.disabled = true;
                if (stickySaveBar) stickySaveBar.classList.add('show');
            } else if (stickySaveBar) {
                stickySaveBar.classList.remove('show');
            }
        }

        updateHistoryButtons();
    }

    /* =========================================================
     * SIMPAN
     *  - role 'all'  -> simpan ke tabel global
     *  - role tertentu -> simpan ke override role itu saja
     * ========================================================= */
    function saveLayout() {
        if (!sortableGridEl) return;

        const snap = getSnapshot();
        const currentOrder = snap.sections;
        const currentCardsOrder = snap.cards;
        const activeRole = (currentRoleFilter && currentRoleFilter !== 'all') ? currentRoleFilter : null;
        const saveBtns = document.querySelectorAll('.btn-save-action');

        saveBtns.forEach(b => { b.disabled = true; });

        Swal.fire({
            title: 'Menyimpan Pengaturan Layout...',
            text: activeRole
                ? `Menyimpan tata letak khusus untuk role "${activeRole}". Role lain tidak terpengaruh.`
                : 'Menerapkan tata letak global section dan kartu.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        const payload = { order: currentOrder, cards_order: currentCardsOrder };
        if (activeRole) payload.role = activeRole;

        fetch(window.layoutSettingConfig?.urls?.update || '/admin/layout-setting/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.layout_state) layoutStateMap = data.layout_state;

            initialOrder = [...currentOrder];
            initialCardOrder = JSON.parse(JSON.stringify(currentCardsOrder));
            historyStack = [getSnapshot()];
            historyIndex = 0;
            checkDirtyState();

            Swal.fire({
                icon: 'success',
                title: 'Berhasil Disimpan!',
                text: data.message || 'Urutan section dan kartu dashboard berhasil disimpan.',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
        })
        .catch(error => {
            console.error('Error saving dashboard layout:', error);
            const errMsg = (error && error.message) ||
                (error && error.errors ? Object.values(error.errors).flat().join(', ') : 'Terjadi kesalahan saat menyimpan urutan.');
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan!',
                text: errMsg,
                showConfirmButton: true
            });
        })
        .finally(() => {
            saveBtns.forEach(b => { b.disabled = false; });
            checkDirtyState();
        });
    }

    /* =========================================================
     * PREVIEW MODAL
     * ========================================================= */
    function populatePreviewModal() {
        const leftCol = byId('preview-left-column');
        const rightCol = byId('preview-right-column');
        const modalLabel = byId('previewModalLabel');
        if (!leftCol || !rightCol || !sortableGridEl) return;

        leftCol.innerHTML = '';
        rightCol.innerHTML = '';

        if (modalLabel) {
            modalLabel.innerHTML = currentRoleFilter !== 'all'
                ? `Simulasi Tampilan Dashboard Home <span class="badge bg-primary fs-6 fw-normal ms-2">Role: ${currentRoleFilter}</span>`
                : `Simulasi Tampilan Dashboard Home <span class="badge bg-secondary fs-6 fw-normal ms-2">Semua Role (Global)</span>`;
        }

        const currentOrder = Array.from(sortableGridEl.querySelectorAll('.sortable-item')).map(el => el.dataset.id);
        const allowedSections = (currentRoleFilter !== 'all') ? (rolePermissionsMap[currentRoleFilter] || {}) : null;

        let displayPos = 0;
        currentOrder.forEach(id => {
            if (allowedSections && !allowedSections[id]) return;
            displayPos++;
            const pos = displayPos;
            const isLeft = (pos % 2 !== 0);
            const color = isLeft ? '#2563eb' : '#059669';
            const meta = sectionMetadata[id] || { name: id, icon: 'users.svg', color: '#475569', desc: '' };

            const cardHtml = `
                <div class="card border-0 shadow-xs p-3 rounded-3" style="border: 2px solid ${color} !important; background: #ffffff;">
                    <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                        <span class="badge rounded-pill text-white fw-bold px-2 py-1" style="background-color: ${color}; font-size: 0.75rem;">#${pos}</span>
                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 32px; height: 32px; background-color: ${color}15; border: 1px solid ${color}30;">
                            <img src="/icon/${meta.icon}" alt="${meta.name}" width="18" height="18">
                        </div>
                        <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                            <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.88rem;">${meta.name}</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">${meta.desc}</p>
                        </div>
                    </div>
                </div>`;

            if (isLeft) {
                leftCol.innerHTML += cardHtml;
            } else {
                rightCol.innerHTML += cardHtml;
            }
        });
    }

    /* =========================================================
     * INISIALISASI
     * ========================================================= */
    document.addEventListener('DOMContentLoaded', function () {
        sortableGridEl = byId('sortable-list');
        sortableTableBodyEl = byId('table-sortable-body');

        // Sortable: grid section
        if (sortableGridEl) {
            gridSortableInstance = new Sortable(sortableGridEl, {
                animation: 250,
                draggable: '.sortable-item',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                handle: '.drag-handle, .category-icon-box',
                onEnd: function () {
                    syncFromGridToTable();
                    updateAllBadgesAndSelects();
                    checkDirtyState();
                    recordHistory();
                }
            });
        }

        // Sortable: tabel section
        if (sortableTableBodyEl) {
            tableSortableInstance = new Sortable(sortableTableBodyEl, {
                animation: 250,
                draggable: '.table-sortable-row',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                handle: '.drag-handle, .category-icon-box',
                onEnd: function () {
                    syncFromTableToGrid();
                    updateAllBadgesAndSelects();
                    checkDirtyState();
                    recordHistory();
                }
            });
        }

        // Sortable: card di tiap section
        document.querySelectorAll('.card-sortable-list').forEach(listEl => {
            const sectionKey = listEl.dataset.section;

            cardSortableInstances[sectionKey] = new Sortable(listEl, {
                animation: 200,
                draggable: '.card-sortable-item',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function () {
                    updateCardBadgesForSection(sectionKey);
                    checkDirtyState();
                    recordHistory();
                }
            });
        });

        // Baseline awal = kondisi halaman saat dimuat (layout global dari server)
        const baseSnap = getSnapshot();
        initialOrder = [...baseSnap.sections];
        initialCardOrder = JSON.parse(JSON.stringify(baseSnap.cards));
        historyStack = [baseSnap];
        historyIndex = 0;
        updateHistoryButtons();

        // Undo / Redo
        const undoBtn = byId('btn-undo-action');
        const redoBtn = byId('btn-redo-action');
        if (undoBtn) undoBtn.addEventListener('click', e => { e.preventDefault(); undo(); });
        if (redoBtn) redoBtn.addEventListener('click', e => { e.preventDefault(); redo(); });

        document.addEventListener('keydown', function (e) {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) return;
            const key = e.key.toLowerCase();
            if ((e.ctrlKey || e.metaKey) && key === 'z') {
                e.preventDefault();
                e.shiftKey ? redo() : undo();
            } else if ((e.ctrlKey || e.metaKey) && key === 'y') {
                e.preventDefault();
                redo();
            }
        });

        // Dropdown posisi (section & card)
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('position-select')) {
                moveItemToIndex(e.target.dataset.id, parseInt(e.target.value, 10));
            } else if (e.target.classList.contains('card-position-select')) {
                moveCardToIndex(e.target.dataset.section, e.target.dataset.id, parseInt(e.target.value, 10));
            }
        });

        // Tombol panah cepat (section & card)
        document.addEventListener('click', function (e) {
            const topBtn = e.target.closest('.btn-move-top');
            const bottomBtn = e.target.closest('.btn-move-bottom');
            const upBtn = e.target.closest('.btn-move-up');
            const downBtn = e.target.closest('.btn-move-down');
            const sectionBtn = topBtn || bottomBtn || upBtn || downBtn;

            if (sectionBtn) {
                const container = sectionBtn.closest('.sortable-item, .table-sortable-row');
                if (container && sortableGridEl) {
                    const itemId = container.dataset.id;
                    const visibleItems = Array.from(sortableGridEl.querySelectorAll('.sortable-item:not(.d-none)'));
                    const currentItem = sortableGridEl.querySelector(`.sortable-item[data-id="${itemId}"]`);
                    const curIdx = visibleItems.indexOf(currentItem) + 1;

                    if (curIdx > 0) {
                        if (topBtn) moveItemToIndex(itemId, 1);
                        else if (bottomBtn) moveItemToIndex(itemId, visibleItems.length);
                        else if (upBtn) moveItemToIndex(itemId, Math.max(1, curIdx - 1));
                        else if (downBtn) moveItemToIndex(itemId, Math.min(visibleItems.length, curIdx + 1));
                    }
                    return;
                }
            }

            const cTop = e.target.closest('.btn-card-move-top');
            const cBottom = e.target.closest('.btn-card-move-bottom');
            const cUp = e.target.closest('.btn-card-move-up');
            const cDown = e.target.closest('.btn-card-move-down');
            const cardBtn = cTop || cBottom || cUp || cDown;

            if (cardBtn) {
                const cardItem = cardBtn.closest('.card-sortable-item');
                if (!cardItem) return;

                const sectionKey = cardItem.dataset.section;
                const cardId = cardItem.dataset.id;
                const listEl = cardItem.closest('.card-sortable-list');
                if (!listEl) return;

                const currentOrder = Array.from(listEl.querySelectorAll('.card-sortable-item')).map(el => el.dataset.id);
                const curIdx = currentOrder.indexOf(cardId) + 1;

                if (cTop) moveCardToIndex(sectionKey, cardId, 1);
                else if (cBottom) moveCardToIndex(sectionKey, cardId, currentOrder.length);
                else if (cUp) moveCardToIndex(sectionKey, cardId, Math.max(1, curIdx - 1));
                else if (cDown) moveCardToIndex(sectionKey, cardId, Math.min(currentOrder.length, curIdx + 1));
            }
        });

        // Tombol Simpan (header + sticky bar)
        document.querySelectorAll('.btn-save-action').forEach(btn => {
            btn.addEventListener('click', saveLayout);
        });

        // Tombol Reset header:
        //  - filter role aktif  -> reset PER ROLE (pilih section / card)
        //  - global             -> reset GLOBAL sesuai tab aktif (section / card)
        document.querySelectorAll('.btn-reset-action').forEach(btn => {
            btn.addEventListener('click', function () {
                if (currentRoleFilter && currentRoleFilter !== 'all') {
                    executeResetDefault();
                    return;
                }
                const cardsContainer = byId('cards-view-container');
                const isCardsMode = cardsContainer && !cardsContainer.classList.contains('d-none');
                runReset(isCardsMode ? 'cards' : 'sections', 'all');
            });
        });

        // Preview modal
        const previewModalEl = byId('previewModal');
        if (previewModalEl) {
            previewModalEl.addEventListener('show.bs.modal', populatePreviewModal);
        }

        // Inisialisasi tampilan awal
        updateAllBadgesAndSelects();
        checkDirtyState();
    });

Object.assign(window, { byId, csrfToken, switchSectionDisplay, getSnapshot, updateHistoryButtons, recordHistory, applySnapshot, undo, redo, updateCardBadgesForSection, moveCardToIndex, syncFromGridToTable, syncFromTableToGrid, buildPositionOptions, updateAllBadgesAndSelects, moveItemToIndex, syncFromGridToCards, updateCardsRoleFilterView, applyLayoutState, applyRoleLayoutState, handleRoleFilterChange, doRoleFilterChange, resetRoleFilter, runReset, executeResetDefault, checkDirtyState, saveLayout, populatePreviewModal });
