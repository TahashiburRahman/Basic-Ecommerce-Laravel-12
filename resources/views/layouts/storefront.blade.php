<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Verdant Market')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    @php
        $navCategories = Illuminate\Support\Facades\Schema::hasTable('categories')
            ? App\Models\Category::where('is_active', true)->take(5)->get()
            : collect();
    @endphp

    <div class="banner-bar text-center">
        Free shipping on orders over $100 • New arrivals every week
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">Verdant Market</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('shop') }}">Shop</a></li>
                    @foreach($navCategories as $category)
                        <li class="nav-item"><a class="nav-link" href="{{ route('category.show', $category) }}">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
                <form class="d-flex me-3" action="{{ route('shop') }}" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </form>
                <a class="btn btn-primary position-relative" href="{{ route('cart') }}">
                    <i class="bi bi-cart3"></i>
                    Cart
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
                        {{ count(session('cart', [])) }}
                    </span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="text-white">Verdant Market</h5>
                    <p>Modern essentials for everyday living, curated with quality and care.</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white">Customer Care</h6>
                    <ul class="list-unstyled">
                        <li>hello@verdantmarket.com</li>
                        <li>+1 (555) 321-2468</li>
                        <li>123 Green Avenue, Portland</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white">Useful Links</h6>
                    <ul class="list-unstyled">
                        <li><a class="text-white-50 text-decoration-none" href="{{ route('shop') }}">Shop</a></li>
                        <li><a class="text-white-50 text-decoration-none" href="{{ route('cart') }}">Cart</a></li>
                        <li><a class="text-white-50 text-decoration-none" href="{{ route('admin.login') }}">Admin Login</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
