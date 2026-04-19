<section>
    <form method="post" action="{{ route('profile.update') }}" class="mt-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_220px] lg:items-start">
            <!-- Left side - Header and Form -->
            <div class="w-full">
                <header>
                    <h2 class="text-lg font-semibold text-slate-900">
                        {{ __('Profile Information') }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-600">
                        {{ __("Update your account's profile information and username.") }}
                    </p>
                </header>

                <div class="mt-6 space-y-5">
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input 
                            id="name" 
                            name="name" 
                            type="text" 
                            class="mt-1 block w-full bg-slate-100 cursor-not-allowed" 
                            :value="old('name', $user->name)" 
                            readonly 
                            autocomplete="name" 
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="username" :value="__('Username')" />
                        <x-text-input 
                            id="username" 
                            name="username" 
                            type="text" 
                            class="mt-1 block w-full" 
                            :value="old('username', $user->username)"
                            required 
                            autocomplete="username"
                            placeholder="Enter your username (lowercase letters, numbers, and underscores only)"
                        />
                        <p class="mt-1 text-xs text-slate-500">
                            Username can only contain lowercase letters, numbers, and underscores.
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('username')" />

                        @if (session('status') === 'profile-updated')
                            <div class="mt-2">
                                <p class="text-sm font-medium text-green-600">
                                    {{ __('Your profile has been updated successfully!') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right side - Profile Picture aligned with header -->
            <div class="flex w-full max-w-[220px] flex-col items-center justify-self-start lg:justify-self-center">
                <x-input-label for="profile_picture" :value="__('Profile Picture')" class="mb-3" />
                
                <!-- Profile Picture Preview -->
                <div class="mb-4">
                    @if ($user->profile_picture)
                        <img id="profile-preview" src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture" style="width: 120px; height: 120px;" class="rounded-full object-cover border-2 border-gray-300">
                    @else
                        <img id="profile-preview" src="{{ asset('images/default-avatar.svg') }}" alt="Default Avatar" style="width: 120px; height: 120px;" class="rounded-full object-cover border-2 border-gray-300">
                    @endif
                </div>

                <!-- Custom File Input Button -->
                <div class="relative">
                    <input 
                        type="file" 
                        id="profile_picture" 
                        name="profile_picture" 
                        accept="image/jpeg,image/png,image/gif,image/jpg"
                        class="hidden"
                        onchange="previewImage(event)"
                    />
                    <label 
                        for="profile_picture" 
                        class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 cursor-pointer"
                    >
                        Choose File
                    </label>
                </div>
                
                <x-input-error class="mt-2" :messages="$errors->get('profile_picture')" />
            </div>
        </div>

        <div class="mt-6 flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('profile-preview');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>

<!-- Profile Update Success Modal -->
<div id="success-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center rounded-full bg-green-100" style="width: 80px; height: 80px;">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" style="width: 64px; height: 64px;">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3 mt-4">Success!</h3>
                <p class="text-base text-gray-600">
                    Your profile has been updated successfully.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Email Verification Sent Modal -->
<div id="email-verification-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center rounded-full bg-amber-100" style="width: 80px; height: 80px;">
                    <svg class="h-12 w-12 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3 mt-4">Check Your Email!</h3>
                <p class="text-base text-gray-600">
                    We've sent a verification link to your new email address. Please check your inbox and click the link to complete the change.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Email Updated Success Modal -->
<div id="email-updated-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center rounded-full bg-green-100" style="width: 80px; height: 80px;">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" style="width: 64px; height: 64px;">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3 mt-4">Email Updated!</h3>
                <p class="text-base text-gray-600">
                    Your email address has been successfully updated.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Password Update Success Modal -->
<div id="password-success-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center rounded-full bg-green-100" style="width: 80px; height: 80px;">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" style="width: 64px; height: 64px;">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3 mt-4">Password Updated!</h3>
                <p class="text-base text-gray-600">
                    Your password has been updated successfully.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Account Deletion Confirmation Modal -->
<div id="delete-confirmation-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center rounded-full bg-red-100" style="width: 80px; height: 80px;">
                    <svg class="h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3 mt-4">Delete Account?</h3>
                <p class="text-base text-gray-600 mb-6">
                    Are you sure you want to delete your account? This action cannot be undone. All your data will be permanently removed.
                </p>

                <!-- Password Confirmation Input -->
                <div class="mb-6 text-left">
                    <label for="delete-password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Confirm Password
                    </label>
                    <input 
                        type="password" 
                        id="delete-password"
                        placeholder="Enter your password"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none text-sm transition duration-200"
                    />
                    <p id="password-error" class="text-xs text-red-600 mt-1 hidden">Password is required</p>
                </div>

                <div class="flex gap-3">
                    <button 
                        id="cancel-delete-btn"
                        class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 text-base font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        id="confirm-delete-btn"
                        class="flex-1 px-6 py-3 bg-red-600 text-white text-base font-medium rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                    >
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Deleting Modal (Processing) -->
<div id="deleting-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center" style="width: 80px; height: 80px;">
                    <svg class="animate-spin h-16 w-16 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3 mt-4">Deleting Account...</h3>
                <p class="text-base text-gray-600">
                    Please wait while we process your request.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Account Deleted Success Modal -->
<div id="deleted-success-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center rounded-full bg-green-100" style="width: 80px; height: 80px;">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" style="width: 64px; height: 64px;">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3 mt-4">Account Deleted</h3>
                <p class="text-base text-gray-600">
                    Your account has been successfully deleted. Redirecting to login...
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal for File Upload -->
<div id="error-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center rounded-full bg-red-100" style="width: 80px; height: 80px;">
                    <svg class="h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3 mt-4">Invalid File Type</h3>
                <p class="text-base text-gray-600 mb-6">
                    Please upload a valid image file. Only JPG, PNG, and GIF formats are allowed (MAX. 2MB).
                </p>
                <button 
                    id="close-error-modal"
                    class="w-full px-6 py-3 bg-red-600 text-white text-base font-medium rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Invalid Email Format Error Modal -->
<div id="invalid-email-modal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center rounded-full bg-red-100" style="width: 80px; height: 80px;">
                    <svg class="h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-3 mt-4">Invalid Email Format</h3>
                <p class="text-base text-gray-600 mb-6">
                    Please enter a valid email address in the format: user@example.com
                </p>
                <button 
                    id="close-invalid-email-modal"
                    class="w-full px-6 py-3 bg-red-600 text-white text-base font-medium rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal overlay styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.show {
        display: flex !important;
    }

    .modal-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        padding: 1rem;
    }

    .modal-content {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        max-width: 28rem;
        width: 100%;
        padding: 2rem;
        position: relative;
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Checkmark styles */
    .checkmark {
        stroke-width: 2;
        stroke: #10b981;
        stroke-miterlimit: 10;
    }

    .checkmark-circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 2;
        stroke-miterlimit: 10;
        stroke: #10b981;
        fill: none;
        animation: strokeCircle 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark-check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        stroke: #10b981;
        stroke-width: 3;
        animation: strokeCheck 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes strokeCircle {
        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes strokeCheck {
        100% {
            stroke-dashoffset: 0;
        }
    }
</style>

<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('profile-preview');
        const errorModal = document.getElementById('error-modal');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            const maxSize = 2 * 1024 * 1024; // 2MB in bytes
            
            // Check file type
            if (!validTypes.includes(file.type)) {
                errorModal.classList.add('show');
                input.value = ''; // Clear the input
                return;
            }
            
            // Check file size
            if (file.size > maxSize) {
                errorModal.classList.add('show');
                input.value = ''; // Clear the input
                return;
            }
            
            // If valid, just preview the image (success modal will show after Save)
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }

    // Show profile update success modal
    @if (session('status') === 'profile-updated')
        document.addEventListener('DOMContentLoaded', function() {
            const successModal = document.getElementById('success-modal');
            successModal.classList.add('show');
            
            // Auto close after 3 seconds
            setTimeout(function() {
                successModal.classList.remove('show');
            }, 3000);
        });
    @endif

    // Show email verification sent modal
    @if (session('status') === 'verification-link-sent')
        document.addEventListener('DOMContentLoaded', function() {
            const emailVerificationModal = document.getElementById('email-verification-modal');
            emailVerificationModal.classList.add('show');
            
            // Auto close after 5 seconds
            setTimeout(function() {
                emailVerificationModal.classList.remove('show');
            }, 5000);
        });
    @endif

    // Show email updated success modal
    @if (session('status') === 'email-updated')
        document.addEventListener('DOMContentLoaded', function() {
            const emailUpdatedModal = document.getElementById('email-updated-modal');
            emailUpdatedModal.classList.add('show');
            
            // Auto close after 3 seconds
            setTimeout(function() {
                emailUpdatedModal.classList.remove('show');
            }, 3000);
        });
    @endif

    // Show password update success modal
    @if (session('status') === 'password-updated')
        document.addEventListener('DOMContentLoaded', function() {
            const passwordSuccessModal = document.getElementById('password-success-modal');
            passwordSuccessModal.classList.add('show');
            
            // Auto close after 3 seconds
            setTimeout(function() {
                passwordSuccessModal.classList.remove('show');
            }, 3000);
        });
    @endif

    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Close error modal functionality
        const closeErrorBtn = document.getElementById('close-error-modal');
        if (closeErrorBtn) {
            closeErrorBtn.addEventListener('click', function() {
                document.getElementById('error-modal').classList.remove('show');
            });
        }

        // Close invalid email modal functionality
        const closeInvalidEmailBtn = document.getElementById('close-invalid-email-modal');
        if (closeInvalidEmailBtn) {
            closeInvalidEmailBtn.addEventListener('click', function() {
                document.getElementById('invalid-email-modal').classList.remove('show');
            });
        }

        // Close modals when clicking outside (on backdrop)
        const modals = ['error-modal', 'invalid-email-modal', 'success-modal', 'email-verification-modal', 'email-updated-modal', 'password-success-modal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.remove('show');
                    }
                });
            }
        });

        // Delete Account Modal Logic
        const deleteConfirmationModal = document.getElementById('delete-confirmation-modal');
        const deletingModal = document.getElementById('deleting-modal');
        const deletedSuccessModal = document.getElementById('deleted-success-modal');
        const deletePasswordInput = document.getElementById('delete-password');
        const passwordError = document.getElementById('password-error');
        const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
        const confirmDeleteBtn = document.getElementById('confirm-delete-btn');

        // Listen for delete account button click
        window.showDeleteConfirmation = function() {
            deleteConfirmationModal.classList.add('show');
            deletePasswordInput.value = '';
            passwordError.classList.add('hidden');
        };

        // Cancel delete
        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', function() {
                deleteConfirmationModal.classList.remove('show');
                deletePasswordInput.value = '';
                passwordError.classList.add('hidden');
            });
        }

        // Confirm delete
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', async function() {
                const password = deletePasswordInput.value;

                if (!password) {
                    passwordError.classList.remove('hidden');
                    return;
                }

                passwordError.classList.add('hidden');
                deleteConfirmationModal.classList.remove('show');
                deletingModal.classList.add('show');

                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('_method', 'DELETE');
                    formData.append('password', password);

                    const response = await fetch('{{ route("profile.destroy") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    deletingModal.classList.remove('show');

                    if (response.ok) {
                        deletedSuccessModal.classList.add('show');
                        setTimeout(function() {
                            window.location.href = '{{ route("login") }}';
                        }, 3000);
                    } else {
                        const data = await response.json();
                        alert(data.message || 'Failed to delete account. Please try again.');
                        deleteConfirmationModal.classList.add('show');
                    }
                } catch (error) {
                    console.error('Error deleting account:', error);
                    deletingModal.classList.remove('show');
                    alert('An error occurred. Please try again.');
                    deleteConfirmationModal.classList.add('show');
                }
            });
        }

        // Close delete confirmation modal when clicking outside
        if (deleteConfirmationModal) {
            deleteConfirmationModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                    deletePasswordInput.value = '';
                    passwordError.classList.add('hidden');
                }
            });
        }
    });
</script>
