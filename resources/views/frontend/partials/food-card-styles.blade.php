{{-- Shared styling for frontend.partials.food-card (menu page + home page) --}}
<style>
    .food-box {
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 18px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all .25s ease;
    }

    .food-box:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 35px rgba(0,0,0,.35);
    }

    .food-img {
        height: 180px;
        background: #111;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .food-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .food-details {
        background: rgba(0,0,0,.8);
        padding: 15px;
        color: #fff;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .food-tag {
        display: inline-block;
        background: rgba(241,184,22,.18);
        color: #f1b816;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
    }

    .food-name {
        font-weight: 700;
        margin: 8px 0 4px;
        color: #fff;
    }

    .food-desc {
        color: rgba(255,255,255,.6);
        font-size: 13px;
        margin-bottom: 0;
    }

    .price-now {
        font-size: 18px;
        font-weight: 700;
        color: #1dbf73;
        display: block;
    }

    .price-was {
        font-size: 13px;
        color: rgba(255,255,255,.45);
        text-decoration: line-through;
    }

    .discount-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: #198754;
        color: #fff;
        padding: 4px 11px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        z-index: 10;
    }

    .food-box .add-cart-btn {
        background: #198754;
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        padding: 0;
        transition: .2s;
    }

    .food-box .add-cart-btn:hover:not(:disabled) {
        background: #157347;
        transform: scale(1.08);
    }

    .food-box .add-cart-btn.disabled-btn {
        background: #6c757d;
        cursor: not-allowed;
    }

    .food-box .details-link,
    .food-box .details-link:hover {
        text-decoration: none;
        color: inherit;
    }

    .menu-empty {
        text-align: center;
        color: rgba(255,255,255,.7);
        padding: 60px 20px;
    }
</style>
