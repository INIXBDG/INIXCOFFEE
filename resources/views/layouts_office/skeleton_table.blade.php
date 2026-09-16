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
<!-- SKELETON TABLE -->
<div id="skeleton-dashboard" class="w-100">
  <!-- Page Header Skeleton -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div class="skeleton-box" style="width: 250px; height: 30px;"></div>
    <div class="skeleton-box" style="width: 150px; height: 20px;"></div>
  </div>

  <!-- Filter/Action Row Skeleton -->
  <div class="d-flex gap-2 mb-4">
      <div class="skeleton-box" style="width: 120px; height: 38px;"></div>
      <div class="skeleton-box" style="width: 100px; height: 38px;"></div>
      <div class="skeleton-box ms-auto" style="width: 200px; height: 38px;"></div>
  </div>

  <!-- Table Card Skeleton -->
  <div class="card border-0 shadow-sm rounded-4 glass-force">
    <div class="card-body p-4">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              @for($i=0; $i<6; $i++)
                <th><div class="skeleton-box" style="width: 100%; height: 20px;"></div></th>
              @endfor
            </tr>
          </thead>
          <tbody>
            @for($row=0; $row<5; $row++)
              <tr>
                @for($col=0; $col<6; $col++)
                  <td><div class="skeleton-box" style="width: 100%; height: 20px;"></div></td>
                @endfor
              </tr>
            @endfor
          </tbody>
        </table>
      </div>

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
    </div>
  </div>
</div>
