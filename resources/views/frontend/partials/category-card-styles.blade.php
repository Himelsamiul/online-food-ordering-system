{{-- Shared styling for frontend.partials.category-cards --}}
<style>
    .cat-card-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 18px;
    }

    /* Menu page variant: one scrollable row instead of a wrapping grid */
    .cat-card-row.is-compact {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 10px;
        scrollbar-width: thin;
    }

    .cat-card-row.is-compact .cat-card {
        flex: 0 0 150px;
    }

    .cat-card-row.is-compact .cat-card-img {
        height: 90px;
    }

    .cat-card {
        display: block;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 18px;
        overflow: hidden;
        text-decoration: none;
        color: #fff;
        transition: all .25s ease;
        cursor: pointer;
    }

    .cat-card:hover {
        transform: translateY(-6px);
        border-color: rgba(241,184,22,.6);
        box-shadow: 0 14px 32px rgba(0,0,0,.4);
        text-decoration: none;
        color: #fff;
    }

    .cat-card.active {
        border-color: #f1b816;
        box-shadow: 0 0 0 2px rgba(241,184,22,.45);
    }

    .cat-card-img {
        height: 130px;
        background: linear-gradient(135deg, #1f1f1f, #101010);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(241,184,22,.55);
        font-size: 34px;
    }

    .cat-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cat-card-body {
        padding: 12px 14px;
        text-align: center;
        background: rgba(0,0,0,.75);
    }

    .cat-card-body h6 {
        font-weight: 700;
        margin: 0 0 2px;
        color: #fff;
        font-size: 15px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cat-card-body small {
        color: #f1b816;
        font-weight: 600;
        font-size: 12px;
    }

    .cat-card.active .cat-card-body {
        background: rgba(241,184,22,.9);
    }

    .cat-card.active .cat-card-body h6 { color: #1c1c1c; }
    .cat-card.active .cat-card-body small { color: rgba(0,0,0,.7); }
</style>
