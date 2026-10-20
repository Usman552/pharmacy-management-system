// ============================================================
// CITY DRUG - Admin Dashboard JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', function() {

    // ─── SIDEBAR TOGGLE ───
    const sidebar = document.getElementById('adminSidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    toggleBtn?.addEventListener('click', () => {
        sidebar?.classList.toggle('open');
        overlay?.classList.toggle('open');
    });
    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('open');
    });

    // ─── SUBMENU ACCORDION ───
    document.querySelectorAll('.has-submenu > a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const submenu = this.nextElementSibling;
            const isOpen = submenu?.style.display === 'block';
            // Close all
            document.querySelectorAll('.sidebar-submenu').forEach(s => s.style.display = 'none');
            document.querySelectorAll('.has-submenu > a').forEach(l => l.setAttribute('aria-expanded', 'false'));
            if(!isOpen && submenu) {
                submenu.style.display = 'block';
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });
    // Open active submenu
    document.querySelector('.sidebar-submenu a.active')?.closest('.sidebar-submenu')?.style && 
        (document.querySelector('.sidebar-submenu a.active').closest('.sidebar-submenu').style.display = 'block');

    // ─── CHARTS (Chart.js) ───
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart')?.getContext('2d');
    if(revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Revenue (Rs.)',
                    data: [85000,92000,78000,110000,98000,125000,118000,134000,145000,138000,152000,168000],
                    borderColor: '#f2971f',
                    backgroundColor: 'rgba(242,151,31,0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#f2971f',
                    pointRadius: 4,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'Nunito', size: 12 } } },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            font: { family: 'Nunito', size: 12 },
                            callback: v => 'Rs.' + (v/1000).toFixed(0) + 'k'
                        }
                    }
                }
            }
        });
    }

    // Orders Chart (Doughnut)
    const ordersCtx = document.getElementById('ordersChart')?.getContext('2d');
    if(ordersCtx) {
        new Chart(ordersCtx, {
            type: 'doughnut',
            data: {
                labels: ['Delivered','Processing','Pending','Cancelled'],
                datasets: [{
                    data: [65, 20, 10, 5],
                    backgroundColor: ['#28a745','#0d6efd','#ffc107','#dc3545'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Nunito', size: 12 }, padding: 16, usePointStyle: true, pointStyleWidth: 8 }
                    }
                }
            }
        });
    }

    // Weekly Sales Bar
    const weeklyCtx = document.getElementById('weeklyChart')?.getContext('2d');
    if(weeklyCtx) {
        new Chart(weeklyCtx, {
            type: 'bar',
            data: {
                labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                datasets: [{
                    label: 'Orders',
                    data: [18,24,16,30,28,42,35],
                    backgroundColor: 'rgba(242,151,31,0.8)',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'Nunito', size: 12 } } },
                    y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { family: 'Nunito', size: 12 } } }
                }
            }
        });
    }

    // Top Categories
    const catCtx = document.getElementById('categoryChart')?.getContext('2d');
    if(catCtx) {
        new Chart(catCtx, {
            type: 'bar',
            data: {
                labels: ['Medicines','Supplements','Personal Care','Baby','Sports','Herbs'],
                datasets: [{
                    data: [35,25,18,10,8,4],
                    backgroundColor: ['#0d6efd','#28a745','#f2971f','#e84393','#8b5cf6','#059669'],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { callback: v => v+'%', font: { family: 'Nunito', size: 12 } } },
                    y: { grid: { display: false }, ticks: { font: { family: 'Nunito', size: 12 } } }
                }
            }
        });
    }

    // ─── DATA TABLES (Search/Filter) ───
    const tableSearch = document.querySelector('.table-search');
    if(tableSearch) {
        tableSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.searchable-table tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    }

    // ─── SELECT ALL CHECKBOX ───
    document.querySelector('#selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        updateBulkActions();
    });
    document.querySelectorAll('.row-check').forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });
    function updateBulkActions() {
        const checked = document.querySelectorAll('.row-check:checked').length;
        const bulkBar = document.querySelector('.bulk-actions-bar');
        if(bulkBar) {
            bulkBar.style.display = checked > 0 ? 'flex' : 'none';
            const countEl = bulkBar.querySelector('.selected-count');
            if(countEl) countEl.textContent = checked + ' selected';
        }
    }

    // ─── IMAGE PREVIEW ───
    document.querySelectorAll('.img-upload-input').forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if(!file) return;
            const reader = new FileReader();
            const preview = this.closest('.admin-form-group')?.querySelector('.img-preview');
            const box = this.closest('.img-upload-box');
            reader.onload = e => {
                if(preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if(box) box.querySelector('i')?.style && (box.querySelector('i').style.display = 'none');
            };
            reader.readAsDataURL(file);
        });
    });

    // ─── CONFIRM DELETE ───
    document.querySelectorAll('.confirm-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if(!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // ─── TOAST ───
    window.showAdminToast = function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning'} position-fixed`;
        toast.style.cssText = 'top:80px;right:20px;z-index:9999;min-width:280px;font-family:Nunito,sans-serif;font-weight:600;font-size:14px;box-shadow:0 4px 20px rgba(0,0,0,0.15);animation:slideIn .3s ease';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3500);
    };

    // Show session toasts
    const sessionToast = document.querySelector('[data-toast]');
    if(sessionToast) { showAdminToast(sessionToast.dataset.toast, sessionToast.dataset.type || 'success'); }

    // ─── STATUS TOGGLE ───
    document.querySelectorAll('.status-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const model = this.dataset.model;
            const status = this.checked ? 1 : 0;
            fetch('/admin/toggle-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ id, model, status })
            }).then(r => r.json()).then(data => {
                showAdminToast(data.message || 'Status updated', 'success');
            }).catch(() => { this.checked = !this.checked; showAdminToast('Error updating status', 'error'); });
        });
    });

    // ─── PRINT RECEIPT ───
    document.querySelector('.print-receipt')?.addEventListener('click', () => window.print());

    // ─── ORDER STATUS UPDATE ───
    document.querySelector('#orderStatusForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(this.action, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                showAdminToast(data.message || 'Order updated', 'success');
                setTimeout(() => location.reload(), 1500);
            });
    });

    console.log('🏥 City Drug Admin - Loaded');
});
