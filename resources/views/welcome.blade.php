<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings & Loans Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .hero-pattern {
            background-image:
                radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/isubyo-logo-modern.svg') }}" alt="isubyo Logo" class="h-10 w-10">
                    <span class="text-xl font-bold text-gray-900">isubyo</span>
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-600 hover:text-gray-900 transition">Features</a>
                    <a href="#how-it-works" class="text-gray-600 hover:text-gray-900 transition">How It Works</a>
                    <a href="#benefits" class="text-gray-600 hover:text-gray-900 transition">Benefits</a>
                </div>

                <div class="flex items-center space-x-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Login</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="gradient-primary text-white px-4 py-2 rounded-lg hover:shadow-lg transition">Sign Up</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden hero-pattern py-20 md:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="space-y-6">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight">
                        Manage Group <span class="gradient-primary bg-clip-text text-transparent">Savings & Loans</span> Easily
                    </h1>
                    <p class="text-xl text-gray-600">
                        A complete accounting system for managing member savings, loans, monthly charges, and financial reporting for multiple groups of people.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="gradient-primary text-white px-8 py-3 rounded-lg hover:shadow-lg transition text-center font-semibold">
                                <i class="fas fa-arrow-right mr-2"></i>Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="gradient-primary text-white px-8 py-3 rounded-lg hover:shadow-lg transition text-center font-semibold">
                                <i class="fas fa-arrow-right mr-2"></i>Get Started
                            </a>
                            <a href="{{ route('login') }}" class="border-2 border-gray-900 text-gray-900 px-8 py-3 rounded-lg hover:bg-gray-50 transition text-center font-semibold">
                                Sign In
                            </a>
                        @endauth
                    </div>

                    <div class="flex items-center space-x-6 pt-4 text-sm text-gray-600">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>Free to Start</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>No Credit Card</span>
                        </div>
                    </div>
                </div>

                <!-- Right Illustration -->
                <div class="relative h-96 md:h-full">
                    <div class="absolute inset-0 gradient-primary opacity-10 rounded-3xl transform -rotate-6"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl p-8 space-y-4">
                        <div class="h-3 bg-gradient-primary rounded-full w-1/3"></div>
                        <div class="h-2 bg-gray-200 rounded-full w-2/3"></div>
                        <div class="h-2 bg-gray-200 rounded-full w-1/2"></div>

                        <div class="grid grid-cols-2 gap-4 pt-8">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-xs text-gray-600">Total Savings</div>
                                <div class="text-2xl font-bold text-gray-900">$45,230</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-xs text-gray-600">Active Loans</div>
                                <div class="text-2xl font-bold text-gray-900">12</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-xs text-gray-600">Members</div>
                                <div class="text-2xl font-bold text-gray-900">45</div>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <div class="text-xs text-gray-600">Interest Earned</div>
                                <div class="text-2xl font-bold text-gray-900">$3,450</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold gradient-primary bg-clip-text text-transparent">100%</div>
                    <p class="text-gray-600 mt-2">Secure & Encrypted</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold gradient-primary bg-clip-text text-transparent">24/7</div>
                    <p class="text-gray-600 mt-2">Availability</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold gradient-primary bg-clip-text text-transparent">Real-time</div>
                    <p class="text-gray-600 mt-2">Reporting</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold gradient-primary bg-clip-text text-transparent">∞</div>
                    <p class="text-gray-600 mt-2">Scalability</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Powerful Features</h2>
                <p class="text-xl text-gray-600">Everything you need to manage group savings and loans</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1: Loan Management -->
                <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
                    <div class="w-12 h-12 gradient-primary rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-handshake text-white text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Loan Management</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Flexible loan terms</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Automatic charge schedule</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Payment tracking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Default detection</li>
                    </ul>
                </div>

                <!-- Feature 2: Savings Management -->
                <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
                    <div class="w-12 h-12 gradient-success rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-piggy-bank text-white text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Savings Management</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Individual accounts</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Deposit/withdrawal tracking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Interest accrual</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Balance management</li>
                    </ul>
                </div>

                <!-- Feature 3: Financial Reporting -->
                <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-chart-bar text-white text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Financial Reporting</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Group dashboard</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Member statements</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Performance metrics</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Default reports</li>
                    </ul>
                </div>

                <!-- Feature 4: Multi-Group Support -->
                <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-users text-white text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Multi-Group Support</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Unlimited groups</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Independent finances</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Role-based access</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Member management</li>
                    </ul>
                </div>

                <!-- Feature 5: Complete Accounting -->
                <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-pink-500 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-book text-white text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Complete Accounting</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Transaction ledger</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Audit trails</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Balance tracking</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Reconciliation</li>
                    </ul>
                </div>

                <!-- Feature 6: Security & Compliance -->
                <div class="bg-white p-8 rounded-xl shadow hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-shield-alt text-white text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Security & Compliance</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Data encryption</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>User authentication</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Role-based permissions</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Activity logging</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-xl text-gray-600">Simple, intuitive workflow for managing savings and loans</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <!-- Loan Workflow -->
                <div class="space-y-6">
                    <h3 class="text-2xl font-bold text-gray-900">Loan Workflow</h3>

                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full gradient-primary text-white font-bold">1</div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Member Applies</h4>
                                <p class="text-gray-600">Member requests loan with amount and duration</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full gradient-primary text-white font-bold">2</div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Admin Approves</h4>
                                <p class="text-gray-600">Group admin reviews and approves the loan</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full gradient-primary text-white font-bold">3</div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Schedule Generated</h4>
                                <p class="text-gray-600">Monthly charges and payment schedule created</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full gradient-primary text-white font-bold">4</div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Funds Disbursed</h4>
                                <p class="text-gray-600">Member receives the borrowed funds</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full gradient-primary text-white font-bold">5</div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Payments Tracked</h4>
                                <p class="text-gray-600">Record principal and charge payments monthly</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Savings Workflow -->
                <div class="space-y-6">
                    <h3 class="text-2xl font-bold text-gray-900">Savings Workflow</h3>

                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full gradient-success text-white font-bold">1</div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Member Joins</h4>
                                <p class="text-gray-600">Member registers and joins a savings group</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full gradient-success text-white font-bold">2</div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Account Created</h4>
                                <p class="text-gray-600">Individual savings account automatically created</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full gradient-success text-white font-bold">3</div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Regular Deposits</h4>
                                <p class="text-gray-600">Member makes regular savings contributions</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full gradient-success text-white font-bold">4</div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Interest Earned</h4>
                                <p class="text-gray-600">Interest accrues on savings balance</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full gradient-success text-white font-bold">5</div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Track Growth</h4>
                                <p class="text-gray-600">View balance and interest earned anytime</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Choose Us?</h2>
                <p class="text-xl text-gray-600">Benefits of using our system</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex gap-4">
                    <div class="flex-shrink-0 text-3xl gradient-primary bg-clip-text text-transparent">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Fast & Easy Setup</h3>
                        <p class="text-gray-600">Get started in minutes with our intuitive interface</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0 text-3xl gradient-primary bg-clip-text text-transparent">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Secure & Reliable</h3>
                        <p class="text-gray-600">Bank-level encryption for your data protection</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0 text-3xl gradient-primary bg-clip-text text-transparent">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Real-time Analytics</h3>
                        <p class="text-gray-600">Get instant insights into group finances</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0 text-3xl gradient-primary bg-clip-text text-transparent">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">24/7 Support</h3>
                        <p class="text-gray-600">Dedicated support team ready to help</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0 text-3xl gradient-primary bg-clip-text text-transparent">
                        <i class="fas fa-expand"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Unlimited Scalability</h3>
                        <p class="text-gray-600">Support unlimited groups and members</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0 text-3xl gradient-primary bg-clip-text text-transparent">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Accessible Anywhere</h3>
                        <p class="text-gray-600">Access your account from any device, anytime</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-primary">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-6">Ready to Get Started?</h2>
            <p class="text-xl text-blue-100 mb-8">Join hundreds of groups managing savings and loans efficiently</p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-white text-purple-600 px-8 py-3 rounded-lg hover:shadow-lg transition font-semibold">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-white text-purple-600 px-8 py-3 rounded-lg hover:shadow-lg transition font-semibold">
                        Create Account
                    </a>
                    <a href="{{ route('login') }}" class="border-2 border-white text-white px-8 py-3 rounded-lg hover:bg-white hover:text-purple-600 transition font-semibold">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-white font-bold mb-4">Product</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="#how-it-works" class="hover:text-white transition">How it works</a></li>
                        <li><a href="#benefits" class="hover:text-white transition">Benefits</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4">Company</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4">Legal</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="hover:text-white transition"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="hover:text-white transition"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="hover:text-white transition"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="hover:text-white transition"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-sm">
                <p>&copy; 2025 FinanceHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
