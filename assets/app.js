/**
 * Market Satis Sistemi - JavaScript
 * @version 2.0.0
 */

// API URL'leri
const API = {
    auth: 'api/auth.php',
    products: 'api/products.php',
    sales: 'api/sales.php'
};

// Uygulama durumu
const state = {
    token: localStorage.getItem('auth_token'),
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
    cart: [],
    currentPage: {
        products: 1,
        sales: 1
    }
};

// DOM Elemanlari
const elements = {};

// Sayfa yuklendiginde
document.addEventListener('DOMContentLoaded', () => {
    initializeElements();
    initializeEventListeners();
    checkAuth();
});

/**
 * DOM elemanlarini initialize et
 */
function initializeElements() {
    // Login
    elements.loginScreen = document.getElementById('loginScreen');
    elements.loginForm = document.getElementById('loginForm');
    elements.loginUsername = document.getElementById('loginUsername');
    elements.loginPassword = document.getElementById('loginPassword');
    elements.loginBtn = document.getElementById('loginBtn');
    elements.loginError = document.getElementById('loginError');

    // Ana uygulama
    elements.mainApp = document.getElementById('mainApp');
    elements.currentUserName = document.getElementById('currentUserName');
    elements.currentUserRole = document.getElementById('currentUserRole');
    elements.logoutBtn = document.getElementById('logoutBtn');
    elements.todaySalesCount = document.getElementById('todaySalesCount');
    elements.todaySalesAmount = document.getElementById('todaySalesAmount');

    // Tab
    elements.tabBtns = document.querySelectorAll('.tab-btn');
    elements.tabContents = document.querySelectorAll('.tab-content');

    // Satis
    elements.barcodeInput = document.getElementById('barcodeInput');
    elements.addToCartBtn = document.getElementById('addToCartBtn');
    elements.productInfo = document.getElementById('productInfo');
    elements.foundProductName = document.getElementById('foundProductName');
    elements.foundProductPrice = document.getElementById('foundProductPrice');
    elements.cartItems = document.getElementById('cartItems');
    elements.cartTotal = document.getElementById('cartTotal');
    elements.completeSaleBtn = document.getElementById('completeSaleBtn');

    // Urun
    elements.productForm = document.getElementById('productForm');
    elements.productBarcode = document.getElementById('productBarcode');
    elements.productName = document.getElementById('productName');
    elements.productPrice = document.getElementById('productPrice');
    elements.productStock = document.getElementById('productStock');
    elements.productsBody = document.getElementById('productsBody');
    elements.productSearch = document.getElementById('productSearch');
    elements.productsPagination = document.getElementById('productsPagination');

    // Gecmis
    elements.salesBody = document.getElementById('salesBody');
    elements.salesPagination = document.getElementById('salesPagination');
    elements.dateFrom = document.getElementById('dateFrom');
    elements.dateTo = document.getElementById('dateTo');
    elements.filterSalesBtn = document.getElementById('filterSalesBtn');

    // Modal
    elements.saleModal = document.getElementById('saleModal');
    elements.saleDetails = document.getElementById('saleDetails');
    elements.closeBtn = document.querySelector('.close-btn');

    // Diger
    elements.notification = document.getElementById('notification');
    elements.loadingOverlay = document.getElementById('loadingOverlay');
}

/**
 * Event listener'lari initialize et
 */
function initializeEventListeners() {
    // Login
    elements.loginForm.addEventListener('submit', handleLogin);
    elements.logoutBtn.addEventListener('click', handleLogout);

    // Tab degistirme
    elements.tabBtns.forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });

    // Satis
    elements.addToCartBtn.addEventListener('click', addToCart);
    elements.barcodeInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addToCart();
        }
    });
    elements.completeSaleBtn.addEventListener('click', completeSale);

    // Urun
    elements.productForm.addEventListener('submit', addProduct);

    // Urun arama (debounce ile)
    let searchTimeout;
    elements.productSearch.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            state.currentPage.products = 1;
            loadProducts();
        }, 300);
    });

    // Satis filtreleme
    elements.filterSalesBtn.addEventListener('click', () => {
        state.currentPage.sales = 1;
        loadSalesHistory();
    });

    // Modal
    elements.closeBtn.addEventListener('click', closeModal);
    elements.saleModal.addEventListener('click', (e) => {
        if (e.target === elements.saleModal) closeModal();
    });

    // ESC ile modal kapat
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
}

/**
 * Auth kontrol
 */
async function checkAuth() {
    if (!state.token) {
        showLoginScreen();
        return;
    }

    try {
        const result = await apiRequest(`${API.auth}?action=check`);
        if (result.success) {
            state.user = result.data.user;
            localStorage.setItem('auth_user', JSON.stringify(state.user));
            showMainApp();
        } else {
            clearAuth();
            showLoginScreen();
        }
    } catch (error) {
        clearAuth();
        showLoginScreen();
    }
}

/**
 * Giris yap
 */
async function handleLogin(e) {
    e.preventDefault();

    const username = elements.loginUsername.value.trim();
    const password = elements.loginPassword.value;

    if (!username || !password) {
        showLoginError('Kullanici adi ve sifre gerekli');
        return;
    }

    setLoginLoading(true);
    hideLoginError();

    try {
        const result = await apiRequest(`${API.auth}?action=login`, 'POST', {
            username,
            password
        });

        if (result.success) {
            state.token = result.data.token;
            state.user = result.data.user;
            localStorage.setItem('auth_token', state.token);
            localStorage.setItem('auth_user', JSON.stringify(state.user));
            showMainApp();
        } else {
            showLoginError(result.error || 'Giris basarisiz');
        }
    } catch (error) {
        showLoginError(error.message || 'Bir hata olustu');
    } finally {
        setLoginLoading(false);
    }
}

/**
 * Cikis yap
 */
async function handleLogout() {
    try {
        await apiRequest(`${API.auth}?action=logout`, 'POST');
    } catch (error) {
        // Hata olsa bile cikis yap
    }

    clearAuth();
    showLoginScreen();
}

/**
 * Auth temizle
 */
function clearAuth() {
    state.token = null;
    state.user = null;
    state.cart = [];
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
}

/**
 * Login ekranini goster
 */
function showLoginScreen() {
    elements.loginScreen.classList.remove('hidden');
    elements.mainApp.classList.add('hidden');
    elements.loginForm.reset();
    elements.loginUsername.focus();
}

/**
 * Ana uygulamayi goster
 */
function showMainApp() {
    elements.loginScreen.classList.add('hidden');
    elements.mainApp.classList.remove('hidden');

    // Kullanici bilgilerini guncelle
    if (state.user) {
        elements.currentUserName.textContent = state.user.full_name;
        elements.currentUserRole.textContent = state.user.role === 'admin' ? 'Yonetici' : 'Kasiyer';
        elements.currentUserRole.className = `user-role ${state.user.role}`;
    }

    // Barkod inputuna focus
    elements.barcodeInput.focus();

    // Verileri yukle
    loadSalesHistory();
}

/**
 * Login hatasini goster
 */
function showLoginError(message) {
    elements.loginError.textContent = message;
    elements.loginError.classList.remove('hidden');
}

/**
 * Login hatasini gizle
 */
function hideLoginError() {
    elements.loginError.classList.add('hidden');
}

/**
 * Login loading durumu
 */
function setLoginLoading(loading) {
    elements.loginBtn.disabled = loading;
    elements.loginBtn.querySelector('.btn-text').classList.toggle('hidden', loading);
    elements.loginBtn.querySelector('.btn-loading').classList.toggle('hidden', !loading);
}

/**
 * Tab degistir
 */
function switchTab(tabId) {
    elements.tabBtns.forEach(b => b.classList.remove('active'));
    elements.tabContents.forEach(c => c.classList.remove('active'));

    document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
    document.getElementById(tabId).classList.add('active');

    if (tabId === 'products') loadProducts();
    if (tabId === 'history') loadSalesHistory();
    if (tabId === 'sales') elements.barcodeInput.focus();
}

/**
 * API istegi
 */
async function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    // Token varsa ekle
    if (state.token) {
        options.headers['Authorization'] = `Bearer ${state.token}`;
    }

    if (data) {
        options.body = JSON.stringify(data);
    }

    const response = await fetch(url, options);
    const result = await response.json();

    // 401 hatasi - oturum suresi dolmus
    if (response.status === 401) {
        clearAuth();
        showLoginScreen();
        throw new Error('Oturum suresi doldu, lutfen tekrar giris yapin');
    }

    if (!response.ok && !result.success) {
        throw new Error(result.error || 'Bir hata olustu');
    }

    return result;
}

/**
 * Barkod ile urun ara
 */
async function searchProduct(barcode) {
    try {
        const result = await apiRequest(`${API.products}?barcode=${encodeURIComponent(barcode)}`);
        return result.success ? result.data : null;
    } catch (error) {
        return null;
    }
}

/**
 * Sepete ekle
 */
async function addToCart() {
    const barcode = elements.barcodeInput.value.trim();
    if (!barcode) return;

    const product = await searchProduct(barcode);

    if (!product) {
        showNotification('Urun bulunamadi!', 'error');
        elements.productInfo.classList.add('hidden');
        return;
    }

    // Urun bilgisini goster
    elements.foundProductName.textContent = product.name;
    elements.foundProductPrice.textContent = `${parseFloat(product.price).toFixed(2)} TL`;
    elements.productInfo.classList.remove('hidden');

    // Sepette var mi kontrol et
    const existingItem = state.cart.find(item => item.product_id === product.id);

    if (existingItem) {
        existingItem.quantity++;
        existingItem.total_price = existingItem.quantity * existingItem.unit_price;
    } else {
        state.cart.push({
            product_id: product.id,
            product_name: product.name,
            product_barcode: product.barcode,
            quantity: 1,
            unit_price: parseFloat(product.price),
            total_price: parseFloat(product.price)
        });
    }

    renderCart();
    elements.barcodeInput.value = '';
    elements.barcodeInput.focus();
}

/**
 * Sepeti render et
 */
function renderCart() {
    if (state.cart.length === 0) {
        elements.cartItems.innerHTML = '<p class="empty-cart">Sepet bos</p>';
        elements.cartTotal.textContent = '0.00 TL';
        elements.completeSaleBtn.disabled = true;
        return;
    }

    let html = '';
    let total = 0;

    state.cart.forEach((item, index) => {
        total += item.total_price;
        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <div class="cart-item-name">${escapeHtml(item.product_name)}</div>
                    <div class="cart-item-price">${item.unit_price.toFixed(2)} TL</div>
                </div>
                <div class="cart-item-qty">
                    <button onclick="updateQuantity(${index}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQuantity(${index}, 1)">+</button>
                </div>
                <div class="cart-item-total">${item.total_price.toFixed(2)} TL</div>
                <button class="remove-btn" onclick="removeFromCart(${index})">X</button>
            </div>
        `;
    });

    elements.cartItems.innerHTML = html;
    elements.cartTotal.textContent = `${total.toFixed(2)} TL`;
    elements.completeSaleBtn.disabled = false;
}

/**
 * Miktar guncelle
 */
function updateQuantity(index, change) {
    state.cart[index].quantity += change;

    if (state.cart[index].quantity <= 0) {
        state.cart.splice(index, 1);
    } else {
        state.cart[index].total_price = state.cart[index].quantity * state.cart[index].unit_price;
    }

    renderCart();
}

/**
 * Sepetten cikar
 */
function removeFromCart(index) {
    state.cart.splice(index, 1);
    renderCart();
}

/**
 * Satisi tamamla
 */
async function completeSale() {
    if (state.cart.length === 0) return;

    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;

    showLoading(true);

    try {
        const result = await apiRequest(API.sales, 'POST', {
            items: state.cart,
            payment_method: paymentMethod
        });

        if (result.success) {
            showNotification(`Satis tamamlandi! Toplam: ${result.data.total_amount.toFixed(2)} TL`);
            state.cart = [];
            renderCart();
            elements.productInfo.classList.add('hidden');

            // Istatistikleri guncelle
            loadSalesHistory();
        } else {
            showNotification(result.error || 'Satis kaydedilemedi', 'error');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    } finally {
        showLoading(false);
    }
}

/**
 * Urunleri yukle
 */
async function loadProducts() {
    const search = elements.productSearch.value.trim();
    let url = `${API.products}?page=${state.currentPage.products}`;

    if (search) {
        url += `&search=${encodeURIComponent(search)}`;
    }

    try {
        const result = await apiRequest(url);

        if (result.success) {
            renderProducts(result.data.items);
            renderPagination(
                elements.productsPagination,
                result.data.pagination,
                (page) => {
                    state.currentPage.products = page;
                    loadProducts();
                }
            );
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Urunleri render et
 */
function renderProducts(products) {
    if (products.length === 0) {
        elements.productsBody.innerHTML = '<tr><td colspan="5" class="empty-message">Urun bulunamadi</td></tr>';
        return;
    }

    let html = '';
    products.forEach(product => {
        html += `
            <tr>
                <td>${escapeHtml(product.barcode)}</td>
                <td>${escapeHtml(product.name)}</td>
                <td>${parseFloat(product.price).toFixed(2)} TL</td>
                <td>${product.stock || 0}</td>
                <td>
                    <button class="delete-btn" onclick="deleteProduct(${product.id})">Sil</button>
                </td>
            </tr>
        `;
    });

    elements.productsBody.innerHTML = html;
}

/**
 * Urun ekle
 */
async function addProduct(e) {
    e.preventDefault();

    const barcode = elements.productBarcode.value.trim();
    const name = elements.productName.value.trim();
    const price = parseFloat(elements.productPrice.value);
    const stock = parseInt(elements.productStock.value) || 0;

    if (!barcode || !name || isNaN(price)) {
        showNotification('Tum alanlari doldurun!', 'error');
        return;
    }

    try {
        const result = await apiRequest(API.products, 'POST', { barcode, name, price, stock });

        if (result.success) {
            showNotification('Urun basariyla eklendi!');
            elements.productForm.reset();
            loadProducts();
        } else {
            showNotification(result.error || 'Urun eklenemedi', 'error');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Urun sil
 */
async function deleteProduct(id) {
    if (!confirm('Bu urunu silmek istediginize emin misiniz?')) return;

    try {
        const result = await apiRequest(API.products, 'DELETE', { id });

        if (result.success) {
            showNotification(result.message || 'Urun silindi!');
            loadProducts();
        } else {
            showNotification(result.error || 'Urun silinemedi', 'error');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Satis gecmisini yukle
 */
async function loadSalesHistory() {
    let url = `${API.sales}?page=${state.currentPage.sales}`;

    const dateFrom = elements.dateFrom.value;
    const dateTo = elements.dateTo.value;

    if (dateFrom) url += `&date_from=${dateFrom}`;
    if (dateTo) url += `&date_to=${dateTo}`;

    try {
        const result = await apiRequest(url);

        if (result.success) {
            renderSalesHistory(result.data.items);
            renderPagination(
                elements.salesPagination,
                result.data.pagination,
                (page) => {
                    state.currentPage.sales = page;
                    loadSalesHistory();
                }
            );

            // Bugunun istatistiklerini guncelle
            if (result.data.today_stats) {
                elements.todaySalesCount.textContent = result.data.today_stats.count;
                elements.todaySalesAmount.textContent = result.data.today_stats.amount.toFixed(2);
            }
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Satis gecmisini render et
 */
function renderSalesHistory(sales) {
    if (sales.length === 0) {
        elements.salesBody.innerHTML = '<tr><td colspan="6" class="empty-message">Satis bulunamadi</td></tr>';
        return;
    }

    const paymentLabels = { cash: 'Nakit', card: 'Kart', other: 'Diger' };

    let html = '';
    sales.forEach(sale => {
        const date = new Date(sale.created_at).toLocaleString('tr-TR');
        html += `
            <tr>
                <td>#${sale.id}</td>
                <td>${date}</td>
                <td>${escapeHtml(sale.cashier_name || '-')}</td>
                <td>${paymentLabels[sale.payment_method] || sale.payment_method}</td>
                <td>${parseFloat(sale.total_amount).toFixed(2)} TL</td>
                <td><button class="detail-btn" onclick="showSaleDetail(${sale.id})">Detay</button></td>
            </tr>
        `;
    });

    elements.salesBody.innerHTML = html;
}

/**
 * Satis detayi goster
 */
async function showSaleDetail(id) {
    try {
        const result = await apiRequest(`${API.sales}?id=${id}`);

        if (!result.success) {
            showNotification(result.error || 'Detay yuklenemedi', 'error');
            return;
        }

        const sale = result.data;
        const date = new Date(sale.created_at).toLocaleString('tr-TR');
        const paymentLabels = { cash: 'Nakit', card: 'Kart', other: 'Diger' };

        let html = `
            <div class="sale-detail-header">
                <p><strong>Satis No:</strong> #${sale.id}</p>
                <p><strong>Tarih:</strong> ${date}</p>
                <p><strong>Kasiyer:</strong> ${escapeHtml(sale.cashier_name || '-')}</p>
                <p><strong>Odeme:</strong> ${paymentLabels[sale.payment_method] || sale.payment_method}</p>
                <p><strong>Toplam:</strong> ${parseFloat(sale.total_amount).toFixed(2)} TL</p>
            </div>
            <table class="sale-items-table">
                <thead>
                    <tr>
                        <th>Urun</th>
                        <th>Adet</th>
                        <th>Birim Fiyat</th>
                        <th>Toplam</th>
                    </tr>
                </thead>
                <tbody>
        `;

        sale.items.forEach(item => {
            html += `
                <tr>
                    <td>${escapeHtml(item.product_name)}</td>
                    <td>${item.quantity}</td>
                    <td>${parseFloat(item.unit_price).toFixed(2)} TL</td>
                    <td>${parseFloat(item.total_price).toFixed(2)} TL</td>
                </tr>
            `;
        });

        html += '</tbody></table>';

        elements.saleDetails.innerHTML = html;
        elements.saleModal.classList.remove('hidden');
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Modal kapat
 */
function closeModal() {
    elements.saleModal.classList.add('hidden');
}

/**
 * Pagination render et
 */
function renderPagination(container, pagination, onClick) {
    if (!pagination || pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    // Onceki butonu
    if (pagination.has_prev) {
        html += `<button class="page-btn" data-page="${pagination.current_page - 1}">&laquo;</button>`;
    }

    // Sayfa numaralari
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.current_page ? 'active' : '';
        html += `<button class="page-btn ${activeClass}" data-page="${i}">${i}</button>`;
    }

    // Sonraki butonu
    if (pagination.has_next) {
        html += `<button class="page-btn" data-page="${pagination.current_page + 1}">&raquo;</button>`;
    }

    container.innerHTML = html;

    // Event listener'lari ekle
    container.querySelectorAll('.page-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            onClick(parseInt(btn.dataset.page));
        });
    });
}

/**
 * Bildirim goster
 */
function showNotification(message, type = 'success') {
    elements.notification.textContent = message;
    elements.notification.className = `notification ${type}`;
    elements.notification.classList.remove('hidden');

    setTimeout(() => {
        elements.notification.classList.add('hidden');
    }, 3000);
}

/**
 * Loading goster/gizle
 */
function showLoading(show) {
    elements.loadingOverlay.classList.toggle('hidden', !show);
}

/**
 * HTML escape
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Global fonksiyonlar (onclick icin)
window.updateQuantity = updateQuantity;
window.removeFromCart = removeFromCart;
window.deleteProduct = deleteProduct;
window.showSaleDetail = showSaleDetail;
