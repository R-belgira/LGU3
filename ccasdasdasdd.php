<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>LGU3 Local Economic Enterprise — Registration</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/register.css">

</head>
<body class="antialiased font-sans">

  <div class="bg-layer"></div>
  <div class="light-band"></div>
  <div class="light-band band2"></div>
  <div class="particles"></div>
  <div class="logo-spot" aria-hidden="true"></div>
  <img src="assets/img/logo.png" alt="" class="watermark" aria-hidden="true" />

  <div class="relative min-h-screen flex items-center justify-center">
    <div class="max-w-6xl w-full mx-auto px-6 lg:px-12">
      <div class="grid grid-cols-1 lg:grid-cols-12 items-center main-grid">

        <!-- LEFT SIDE -->
        <div class="lg:col-span-7 text-white relative z-10">
          <div class="max-w-xl space-y-6">
            <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
              Join LGU3 Local Economic Enterprise
            </h1>
            <p class="mt-2 text-lg text-blue-100/95">
              Empowering Local Businesses • Driving Growth • Building the Future
            </p>

            <p class="mt-4 text-lg text-blue-50/90 float-y">
              Create your LGU3 Enterprise account today to register businesses,
              manage services, and access digital tools designed for transparent,
              efficient, and community-driven development.
            </p>

            <!-- Feature badges -->
            <div class="mt-6 flex flex-wrap gap-3">
              <span class="inline-flex items-center gap-2 bg-white/8 text-white text-sm px-3 py-2 rounded-full glass">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 13l4 4L19 7" />
                </svg>
                Fast Business Registration
              </span>
              <span class="inline-flex items-center gap-2 bg-white/8 text-white text-sm px-3 py-2 rounded-full glass">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 7h18M3 12h18M3 17h18" />
                </svg>
                Transparent Processing
              </span>
              <span class="inline-flex items-center gap-2 bg-white/8 text-white text-sm px-3 py-2 rounded-full glass">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 6h16M4 10h16M4 14h16" />
                </svg>
                Community Access
              </span>
            </div>

            <div class="mt-8">
              <a href="#" class="inline-flex items-center gap-3 px-5 py-3 rounded-xl font-medium btn-prim text-white shadow-lg hover:shadow-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11.5V11a1 1 0 01-2 0V6.5H7a1 1 0 010-2h4a1 1 0 010 2h-2z" clip-rule="evenodd"/></svg>
                Learn More About LGU Services
              </a>
            </div>

            <div class="mt-10 text-sm text-blue-100/70">
              <strong>Contact:</strong> info@lgu3.gov.ph • (02) 1234-5678
            </div>
          </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="lg:col-span-5 relative z-20 flex items-center justify-center">
          <div class="w-full max-w-md glass rounded-2xl p-1">
            <div class="card-inner">

              <div class="flex flex-col items-center">
                <img src="assets/img/logo.png" alt="LGU Logo" class="w-28 h-28 object-contain mb-3 drop-shadow-lg" />
                <h2 class="text-2xl font-bold text-slate-900">Create your account</h2>
                <p class="text-sm text-muted mt-1">Register to access the LGU3 Enterprise Portal</p>
              </div>

              <!-- REGISTRATION FORM -->
              <form class="mt-6 space-y-4" action="actions/register.php" method="POST">

                <!-- Full Name -->
                <label class="block">
                  <div class="relative">
                    <span class="absolute left-3 top-3 text-slate-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c2.485 0 4.5-2.015 4.5-4.5S14.485 2 12 2 7.5 4.015 7.5 6.5 9.515 11 12 11zM4 20c0-3.313 3.134-6 8-6s8 2.687 8 6"/>
                      </svg>
                    </span>
                    <input aria-label="Full Name" name="name" type="text" required placeholder="Full Name"
                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 input-focus" />
                  </div>
                </label>

                <!-- Email -->
                <label class="block">
                  <div class="relative">
                    <span class="absolute left-3 top-3 text-slate-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16v12H4z" />
                      </svg>
                    </span>
                    <input aria-label="Email" name="email" type="email" required placeholder="Email address"
                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 input-focus" />
                  </div>
                </label>

                <!-- Password and Confirm Password -->
                <div class="grid grid-cols-2 gap-3">
                  <!-- Password -->
                  <div class="relative">
                    <span class="absolute left-3 top-3 text-slate-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c1.104 0 2 .896 2 2v3H10v-3c0-1.104.896-2 2-2zM8 11V7a4 4 0 118 0v4" />
                      </svg>
                    </span>
                    <input aria-label="Password" name="password" id="password" type="password" required placeholder="Password"
                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 input-focus" />
                  </div>

                  <!-- Confirm Password -->
                  <div class="relative">
                    <span class="absolute left-3 top-3 text-slate-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c1.104 0 2 .896 2 2v3H10v-3c0-1.104.896-2 2-2zM8 11V7a4 4 0 118 0v4" />
                      </svg>
                    </span>
                    <input aria-label="Confirm Password" name="confirm_password" id="confirm_password" type="password" required placeholder="Confirm"
                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 input-focus" />
                  </div>
                </div>

                <!-- Show Password Checkbox -->
                <div class="flex items-center space-x-2 mt-1">
                  <input id="showPassword" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded" onclick="togglePasswordVisibility()">
                  <label for="showPassword" class="text-sm text-slate-700 select-none">Show Password</label>
                </div>

                <!-- Submit -->
                <div>
                  <button type="submit" class="w-full rounded-xl py-3 text-white font-semibold btn-prim transition transform">
                    Create Account
                  </button>
                </div>
              </form>

              <div class="mt-5 text-center">
                <p class="text-sm text-slate-600">Already have an account?
                  <a href="index.php" class="text-blue-600 font-medium hover:underline">Sign in</a>
                </p>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="absolute bottom-6 left-0 right-0 text-center z-30">
    <div class="text-xs text-white/80">© <span id="year"></span> LGU3 Local Economic Enterprise • All Rights Reserved</div>
  </footer>

  <script>
    // Show password checkbox functionality
    function togglePasswordVisibility() {
      const password = document.getElementById('password');
      const confirm = document.getElementById('confirm_password');
      const show = document.getElementById('showPassword');
      const type = show.checked ? 'text' : 'password';
      password.type = confirm.type = type;
    }

    // Dynamic year
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>

</body>
</html>
