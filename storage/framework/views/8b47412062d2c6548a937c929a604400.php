<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

<div class="card mb-3 border-0 shadow-sm">

    <div class="card-body p-4">

        <div class="pt-2 pb-3">

            <div class="text-center mb-3">

                <img src="<?php echo e(asset('assets/img/Logo UKRI.png')); ?>"
                     style="height:80px">

            </div>

            <h5 class="card-title text-center pb-0 fs-4 fw-bold">

                UKRI SSO

            </h5>

            <p class="text-center small text-muted">

                Centralized Authentication System

            </p>

        </div>

        <form class="row g-3"
              action="<?php echo e(route('login')); ?>"
              method="POST">

            <?php echo csrf_field(); ?>

            
            <div class="col-12">

                <label class="form-label fw-semibold">

                    Username

                </label>

                <input type="text"
                       name="username"
                       class="form-control <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('username')); ?>"
                       required
                       autofocus>

                <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                    <div class="invalid-feedback">

                        <?php echo e($message); ?>


                    </div>

                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            </div>

            
            <div class="col-12">

                <label class="form-label fw-semibold">

                    Password

                </label>

                <div class="input-group">

                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           required>

                    <span class="input-group-text bg-white"
                          id="togglePassword"
                          style="cursor:pointer;">

                        <i class="bi bi-eye"
                           id="eyeIcon"></i>

                    </span>

                </div>

                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>

                    <div class="invalid-feedback d-block">

                        <?php echo e($message); ?>


                    </div>

                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            </div>

            
            <div class="col-12">

                <div class="form-check">

                    <input type="checkbox"
                           name="remember"
                           class="form-check-input">

                    <label class="form-check-label">

                        Remember Me

                    </label>

                </div>

            </div>

            
            <div class="col-12">

                <button class="btn btn-primary w-100 fw-bold"
                        type="submit">

                    Login

                </button>

            </div>

        </form>

    </div>

</div>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const togglePassword = document.getElementById('togglePassword');

    const passwordInput = document.getElementById('password');

    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function () {

        const type = passwordInput.getAttribute('type') === 'password'
            ? 'text'
            : 'password';

        passwordInput.setAttribute('type', type);

        eyeIcon.classList.toggle('bi-eye');

        eyeIcon.classList.toggle('bi-eye-slash');

    });

});

</script>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?><?php /**PATH D:\project\simantap\ris-ukri\resources\views/auth/login.blade.php ENDPATH**/ ?>