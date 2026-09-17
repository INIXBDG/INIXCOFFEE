<!-- MULAI SKELETON DASHBOARD -->
<style>
.skeleton-box {
    display: inline-block;
    position: relative;
    overflow: hidden;
    background-color: #e6e8eb;
    border-radius: 4px;
}

.skeleton-box::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    transform: translateX(-100%);
    background-image: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0,
        rgba(255, 255, 255, 0.5) 20%,
        rgba(255, 255, 255, 0.8) 60%,
        rgba(255, 255, 255, 0)
    );
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}
</style>
<div id="skeleton-dashboard" class="w-100">
  <!-- Page Header Skeleton -->
  <div class="d-flex justify-content-between align-items-center mb-5">
    <div class="skeleton-box" style="width: 250px; height: 30px;"></div>
    <div class="skeleton-box" style="width: 150px; height: 20px;"></div>
  </div>

  <!-- Total Karyawan Card Skeleton -->
  <div class="row mb-5">
    <div class="col-12">
      <div class="card border-0 shadow-lg rounded-4 overflow-hidden glass-force">
        <div class="card-body p-4">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="skeleton-box rounded-circle" style="width: 80px; height: 80px;"></div>
            </div>
            <div class="flex-grow-1 ms-4">
              <div class="skeleton-box mb-3" style="width: 140px; height: 18px;"></div>
              <div class="skeleton-box" style="width: 80px; height: 36px;"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Divisi Stats Cards Skeleton -->
  <div class="row mb-5 g-4">
    @for($i = 0; $i < 4; $i++)
    <div class="col-xl-3 col-md-6">
      <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden glass-force">
        <div class="card-body p-4">
          <div class="d-flex align-items-center mb-3">
            <div class="flex-shrink-0">
              <div class="skeleton-box rounded-pill" style="width: 48px; height: 48px;"></div>
            </div>
            <div class="flex-grow-1 ms-3">
              <div class="skeleton-box mb-2" style="width: 100px; height: 14px;"></div>
              <div class="skeleton-box" style="width: 60px; height: 28px;"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endfor
  </div>

  <!-- Tagihan Perusahaan Skeleton -->
  <div class="row g-4 mb-5">
    <div class="col-12">
      <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden glass-force">
        <div class="card-header border-bottom-0 pb-0 d-flex justify-content-between align-items-center">
          <div class="skeleton-box" style="width: 200px; height: 24px;"></div>
          <div class="skeleton-box" style="width: 120px; height: 38px;"></div>
        </div>
        <div class="card-body p-4 mb-4" style="height: 320px;">
          <div class="skeleton-box w-100 mb-3" style="height: 36px;"></div>
          <div class="skeleton-box w-100 mb-3" style="height: 36px;"></div>
          <div class="skeleton-box w-100 mb-3" style="height: 36px;"></div>
          <div class="skeleton-box w-100" style="height: 36px;"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Grafik Outstanding & Ketepatan Skeleton -->
  <div class="row g-4 mb-5">
    @for($i = 0; $i < 2; $i++)
    <div class="col-12">
      <div class="card border-0 shadow-lg h-100 rounded-4 overflow-hidden mb-4 glass-force">
        <div class="card-header border-bottom-0 pb-3">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="skeleton-box" style="width: 280px; height: 24px;"></div>
            <div class="skeleton-box" style="width: 140px; height: 38px;"></div>
          </div>
        </div>
        <div class="card-body p-4">
          <div class="row h-100">
            <div class="col-lg-8">
              <div class="skeleton-box w-100 rounded" style="height: 380px;"></div>
            </div>
            <div class="col-lg-4 d-flex flex-column gap-3 mt-4 mt-lg-0">
              <div class="p-3 rounded-3 shadow-sm border">
                <div class="skeleton-box mb-3" style="width: 150px; height: 18px;"></div>
                <div class="skeleton-box w-100 rounded" style="height: 100px;"></div>
              </div>
              <div class="p-3 rounded-3 shadow-sm border">
                <div class="skeleton-box mb-3" style="width: 150px; height: 18px;"></div>
                <div class="skeleton-box w-100 rounded" style="height: 100px;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endfor
  </div>
</div>
<!-- AKHIR SKELETON DASHBOARD -->

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof jQuery !== 'undefined') {
            setTimeout(function() {
                $('#skeleton-dashboard').fadeOut(300, function() {
                    $('#real-dashboard').removeClass('d-none').hide().fadeIn(400);
                    window.dispatchEvent(new Event('resize'));
                });
            }, 100);
        } else {
            setTimeout(function() {
                var skeleton = document.getElementById('skeleton-dashboard');
                var real = document.getElementById('real-dashboard');
                if(skeleton && real) {
                    skeleton.style.display = 'none';
                    real.classList.remove('d-none');
                    real.style.display = 'block';
                    window.dispatchEvent(new Event('resize'));
                }
            }, 100);
        }
    });
</script>
