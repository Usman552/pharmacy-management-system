/**
 * CityDrug storefront scripts.
 * Prices and stock are always decided by the server; this file only talks to it.
 */
'use strict';

function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.content : '';
}

function postJSON(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        body: JSON.stringify(body || {})
    }).then(function (r) {
        return r.json().catch(function () { return {}; }).then(function (data) { data._ok = r.ok; return data; });
    });
}

/* ── Toast (textContent only, never HTML) ── */
function showToast(msg, type) {
    var stack = document.getElementById('toastStack');
    if (!stack) {
        stack = document.createElement('div');
        stack.id = 'toastStack';
        stack.className = 'toast-stack';
        stack.setAttribute('role', 'status');
        stack.setAttribute('aria-live', 'polite');
        document.body.appendChild(stack);
    }
    var t = document.createElement('div');
    t.className = 'toast-cd ' + (type || 'success');
    var icon = document.createElement('i');
    icon.className = 'fas ' + (type === 'error' ? 'fa-circle-exclamation' : type === 'info' ? 'fa-circle-info' : 'fa-circle-check');
    icon.style.color = type === 'error' ? '#B42318' : '#087F5B';
    var text = document.createElement('span');
    text.textContent = msg;
    t.appendChild(icon); t.appendChild(text);
    stack.appendChild(t);
    setTimeout(function () { t.style.transition = 'opacity .3s'; t.style.opacity = '0'; setTimeout(function () { t.remove(); }, 320); }, 3200);
}

/* ── Cart ── */
function updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(function (el) {
        el.textContent = count > 0 ? count : '';
        el.style.display = count > 0 ? '' : 'none';
    });
}

function openCartSidebar() {
    var s = document.getElementById('cartSidebar'), o = document.getElementById('cartOverlay');
    if (s) { s.classList.add('open'); document.body.style.overflow = 'hidden'; }
    if (o) o.classList.add('open');
}
function closeCartSidebar() {
    var s = document.getElementById('cartSidebar'), o = document.getElementById('cartOverlay');
    if (s) { s.classList.remove('open'); document.body.style.overflow = ''; }
    if (o) o.classList.remove('open');
}

function refreshCartSidebar() {
    var body = document.getElementById('cartBody');
    if (!body) return Promise.resolve();
    return fetch('/cart/items', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.text(); })
        .then(function (html) { body.innerHTML = html; bindCartEvents(body); })
        .catch(function () {});
}

function addToCart(productId, btn, qty) {
    var original = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }
    postJSON('/cart/add', { product_id: productId, quantity: qty || 1 }).then(function (data) {
        if (data.success) {
            showToast(data.message || 'Added to cart');
            updateCartCount(data.count);
            refreshCartSidebar().then(openCartSidebar);
        } else {
            showToast(data.message || 'Could not add to cart', 'error');
        }
    }).catch(function () {
        showToast('Connection problem. Please try again.', 'error');
    }).then(function () {
        if (btn) { btn.disabled = false; btn.innerHTML = original; }
    });
}

function changeQty(cartId, qty, reload) {
    postJSON('/cart/update', { cart_id: cartId, quantity: qty }).then(function (data) {
        if (!data.success) showToast(data.message || 'Could not update quantity', 'error');
        if (typeof data.count !== 'undefined') updateCartCount(data.count);
        if (reload) window.location.reload(); else refreshCartSidebar();
    });
}

function removeFromCart(cartId, reload) {
    postJSON('/cart/remove', { cart_id: cartId }).then(function (data) {
        if (typeof data.count !== 'undefined') updateCartCount(data.count);
        if (reload) window.location.reload(); else refreshCartSidebar();
    });
}

/* Quantity +/- and remove buttons (drawer and cart page) */
function bindCartEvents(root) {
    (root || document).querySelectorAll('[data-qty-step]').forEach(function (btn) {
        btn.onclick = function () {
            var wrap = btn.closest('[data-qty]');
            var cur = parseInt(wrap.dataset.qty, 10) || 1;
            var next = Math.max(1, cur + parseInt(btn.dataset.qtyStep, 10));
            if (next === cur) return;
            changeQty(wrap.dataset.cartId, next, wrap.dataset.reload === '1');
        };
    });
    (root || document).querySelectorAll('[data-cart-remove]').forEach(function (btn) {
        btn.onclick = function () { removeFromCart(btn.dataset.cartRemove, btn.dataset.reload === '1'); };
    });
}

/* ── Wishlist ── */
function toggleWishlist(productId, btn) {
    postJSON('/wishlist/toggle', { product_id: productId }).then(function (data) {
        var icon = btn ? btn.querySelector('i') : null;
        if (data.added) {
            if (icon) icon.className = 'fas fa-heart';
            if (btn) btn.classList.add('wishlisted');
            showToast('Saved to your wishlist');
        } else {
            if (icon) icon.className = 'far fa-heart';
            if (btn) btn.classList.remove('wishlisted');
            showToast('Removed from wishlist', 'info');
        }
        document.querySelectorAll('.wishlist-count').forEach(function (el) {
            el.textContent = data.count > 0 ? data.count : '';
            el.style.display = data.count > 0 ? '' : 'none';
        });
    }).catch(function () { showToast('Please try again', 'error'); });
}

/* ── Page wiring ── */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.open-cart').forEach(function (el) {
        el.addEventListener('click', function (e) { e.preventDefault(); refreshCartSidebar().then(openCartSidebar); });
    });
    document.querySelectorAll('.close-cart').forEach(function (el) { el.addEventListener('click', closeCartSidebar); });
    var co = document.getElementById('cartOverlay');
    if (co) co.addEventListener('click', closeCartSidebar);
    bindCartEvents(document);

    // Mobile navigation
    var nav = document.getElementById('mobileNav'), nov = document.getElementById('mobileNavOverlay');
    function setNav(open) {
        if (!nav) return;
        nav.classList.toggle('open', open);
        if (nov) nov.classList.toggle('open', open);
        document.body.style.overflow = open ? 'hidden' : '';
        var b = document.getElementById('mobileMenuBtn');
        if (b) b.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    var mb = document.getElementById('mobileMenuBtn');
    if (mb) mb.addEventListener('click', function () { setNav(true); });
    var mc = document.getElementById('mobileNavClose');
    if (mc) mc.addEventListener('click', function () { setNav(false); });
    if (nov) nov.addEventListener('click', function () { setNav(false); });

    // Search panel
    var st = document.getElementById('searchToggle'), sp = document.getElementById('searchPanel');
    function setSearch(open) {
        if (!sp || !st) return;
        sp.classList.toggle('open', open);
        st.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) { var i = document.getElementById('searchInput'); if (i) i.focus(); }
    }
    if (st) st.addEventListener('click', function () { setSearch(!sp.classList.contains('open')); });

    // Category menu
    var cm = document.querySelector('.cat-menu');
    if (cm) {
        var cb = cm.querySelector('button');
        cb.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = cm.classList.toggle('open');
            cb.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) { if (!cm.contains(e.target)) { cm.classList.remove('open'); cb.setAttribute('aria-expanded', 'false'); } });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeCartSidebar(); setNav(false); setSearch(false); if (cm) cm.classList.remove('open'); }
    });

    // Header shadow once the page scrolls
    var hd = document.querySelector('.site-header');
    if (hd) {
        var onScroll = function () { hd.classList.toggle('scrolled', window.scrollY > 8); };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // Back to top
    var btt = document.getElementById('backToTop');
    if (btt) {
        window.addEventListener('scroll', function () { btt.classList.toggle('visible', window.scrollY > 500); }, { passive: true });
        btt.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
    }

    // Shop filter toggle on small screens
    var ft = document.getElementById('filterToggle');
    if (ft) ft.addEventListener('click', function () {
        var f = document.getElementById('filters');
        var open = f.classList.toggle('open');
        ft.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    // Auto-submit sort select
    document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
        el.addEventListener('change', function () { el.form.submit(); });
    });

    // Tabs on product page
    document.querySelectorAll('.tabs-cd button').forEach(function (b) {
        b.addEventListener('click', function () {
            document.querySelectorAll('.tabs-cd button').forEach(function (x) { x.classList.remove('on'); x.setAttribute('aria-selected', 'false'); });
            document.querySelectorAll('.tab-pane').forEach(function (p) { p.classList.remove('on'); });
            b.classList.add('on'); b.setAttribute('aria-selected', 'true');
            var pane = document.getElementById(b.dataset.tab);
            if (pane) pane.classList.add('on');
        });
    });

    // Gallery thumbnails
    document.querySelectorAll('[data-gallery-src]').forEach(function (t) {
        t.addEventListener('click', function () {
            var main = document.getElementById('galleryMain');
            if (main) main.src = t.dataset.gallerySrc;
            document.querySelectorAll('[data-gallery-src]').forEach(function (x) { x.classList.remove('on'); });
            t.classList.add('on');
        });
    });

    // Product page quantity picker
    var qi = document.getElementById('pdQty');
    if (qi) {
        var max = parseInt(qi.dataset.max, 10) || 1;
        document.querySelectorAll('[data-pdqty]').forEach(function (b) {
            b.addEventListener('click', function () {
                var v = (parseInt(qi.value, 10) || 1) + parseInt(b.dataset.pdqty, 10);
                qi.value = Math.min(max, Math.max(1, v));
            });
        });
        var addBtn = document.getElementById('pdAdd');
        if (addBtn) addBtn.addEventListener('click', function () {
            addToCart(parseInt(addBtn.dataset.id, 10), addBtn, Math.min(max, Math.max(1, parseInt(qi.value, 10) || 1)));
        });
    }

    // Dismiss flash messages
    document.querySelectorAll('.alert-cd[data-autohide]').forEach(function (el) {
        setTimeout(function () { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(function () { el.remove(); }, 420); }, 6000);
    });
});
