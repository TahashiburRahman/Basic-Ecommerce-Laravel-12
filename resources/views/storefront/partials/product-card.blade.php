<article class="product-card">
    <a href="{{ route('product.show', $product) }}" class="product-card__image-link" aria-label="View {{ $product->name }}">
        <span class="product-card__image-wrapper">
            <img
                src="{{ $product->image_url }}"
                class="product-card__image"
                alt="{{ $product->name }}"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-product.svg') }}';"
            >
        </span>
    </a>
    <div class="product-card__content">
        <span class="product-card__category">{{ $product->category->name ?? 'General' }}</span>
        <h3 class="product-card__title">
            <a href="{{ route('product.show', $product) }}">{{ $product->name }}</a>
        </h3>
        <div class="product-card__price-row">
            <span class="product-card__current-price">${{ number_format($product->current_price, 2) }}</span>
            @if($product->sale_price)
                <span class="product-card__regular-price">${{ number_format($product->price, 2) }}</span>
                <span class="product-card__sale-badge">Sale</span>
            @endif
        </div>
        <div class="product-card__actions">
            <form action="{{ route('cart.add') }}" method="POST" class="product-card__cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary product-card__button">Add to Cart</button>
            </form>
            <a href="{{ route('product.show', $product) }}" class="btn btn-outline-primary product-card__button">View Product</a>
        </div>
    </div>
</article>
