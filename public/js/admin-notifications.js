/**
 * CityDrug Admin — Notifications
 */
'use strict';

function toggleNotifications() {
    var dd = document.getElementById('notifDropdown');
    if (!dd) return;
    var isOpen = dd.style.display !== 'none' && dd.style.display !== '';
    dd.style.display = isOpen ? 'none' : 'block';
    if (!isOpen) loadNotifications();
}

document.addEventListener('click', function(e) {
    var dd = document.getElementById('notifDropdown');
    if (!dd) return;
    if (!e.target.closest('#notifBtn') && !e.target.closest('#notifDropdown')) {
        dd.style.display = 'none';
    }
});

function loadNotifications() {
    var url = document.getElementById('notifDropdown')?.dataset?.url;
    if (!url) return;
    fetch(url)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var list = document.getElementById('notifList');
            var dot  = document.getElementById('notifDot');
            var cnt  = document.getElementById('notifCount');
            var html = '';

            if (data.orders && data.orders.length) {
                html += '<div style="padding:8px 16px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;font-weight:700">New Orders</div>';
                data.orders.forEach(function(o) {
                    html += '<a href="/admin/orders/' + o.id + '" style="display:flex;align-items:center;gap:12px;padding:10px 16px;text-decoration:none;color:#1e293b" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'\'">'
                          + '<div style="width:36px;height:36px;background:#eff6ff;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">🛍️</div>'
                          + '<div><div style="font-size:13px;font-weight:600">Order #' + o.number + '</div>'
                          + '<div style="font-size:12px;color:#64748b">Rs. ' + Number(o.total).toLocaleString() + ' · ' + o.status + '</div></div>'
                          + '</a>';
                });
            }

            if (data.lowStock && data.lowStock.length) {
                html += '<div style="padding:8px 16px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;font-weight:700;margin-top:4px">Low Stock Alert</div>';
                data.lowStock.forEach(function(p) {
                    html += '<a href="/admin/products/' + p.id + '/edit" style="display:flex;align-items:center;gap:12px;padding:10px 16px;text-decoration:none;color:#1e293b" onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'\'">'
                          + '<div style="width:36px;height:36px;background:#fff7ed;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">⚠️</div>'
                          + '<div><div style="font-size:13px;font-weight:600">' + p.name.substring(0, 30) + '</div>'
                          + '<div style="font-size:12px;color:#ef4444">Only ' + p.stock + ' left in stock</div></div>'
                          + '</a>';
                });
            }

            if (!html) {
                html = '<div style="text-align:center;padding:24px;color:#94a3b8">'
                     + '<i class="fas fa-check-circle" style="font-size:24px;display:block;margin-bottom:8px"></i>'
                     + 'All caught up!</div>';
            }

            if (list) list.innerHTML = html;

            if (data.count > 0) {
                if (dot) dot.style.display = 'block';
                if (cnt) cnt.textContent = data.count;
            } else {
                if (dot) dot.style.display = 'none';
                if (cnt) cnt.textContent = '';
            }
        })
        .catch(function() {});
}

// Auto-check on page load
document.addEventListener('DOMContentLoaded', function() {
    var url = document.getElementById('notifDropdown')?.dataset?.url;
    if (!url) return;
    setTimeout(function() {
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.count > 0) {
                    var dot = document.getElementById('notifDot');
                    var cnt = document.getElementById('notifCount');
                    if (dot) dot.style.display = 'block';
                    if (cnt) cnt.textContent = data.count;
                }
            })
            .catch(function() {});
    }, 800);
});
