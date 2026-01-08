/**
 * Market Satis Sistemi - JavaScript
 * @version 2.0.0
 */

// API URL'leri
const API = {
    auth: 'api/auth.php',
    products: 'api/products.php',
    sales: 'api/sales.php',
    orders: 'api/orders.php'
};

// Uygulama durumu
const state = {
    token: localStorage.getItem('auth_token'),
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
    cart: [],
    products: [],
    users: [],
    packages: [],
    favorites: JSON.parse(localStorage.getItem('favorite_products') || '[]'),
    currentPage: {
        products: 1,
        sales: 1
    },
    barcodeScanner: null,
    torchOn: false,
    allProducts: [] // Tum urunler (urun listesi icin)
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
    elements.cameraScanBtn = document.getElementById('cameraScanBtn');
    elements.barcodeScanner = document.getElementById('barcodeScanner');
    elements.closeScannerBtn = document.getElementById('closeScannerBtn');
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

    // Urun Duzenleme Modal
    elements.editProductModal = document.getElementById('editProductModal');
    elements.editProductForm = document.getElementById('editProductForm');
    elements.editProductId = document.getElementById('editProductId');
    elements.editProductBarcode = document.getElementById('editProductBarcode');
    elements.editProductName = document.getElementById('editProductName');
    elements.editProductPrice = document.getElementById('editProductPrice');
    elements.editProductStock = document.getElementById('editProductStock');

    // Kullanici Yonetimi
    elements.userForm = document.getElementById('userForm');
    elements.newUsername = document.getElementById('newUsername');
    elements.newFullName = document.getElementById('newFullName');
    elements.newPassword = document.getElementById('newPassword');
    elements.newRole = document.getElementById('newRole');
    elements.usersBody = document.getElementById('usersBody');

    // Kullanici Duzenleme Modal
    elements.editUserModal = document.getElementById('editUserModal');
    elements.editUserForm = document.getElementById('editUserForm');
    elements.editUserId = document.getElementById('editUserId');
    elements.editUserFullName = document.getElementById('editUserFullName');
    elements.editUserRole = document.getElementById('editUserRole');
    elements.editUserActive = document.getElementById('editUserActive');

    // Paket Siparisler
    elements.packagesGrid = document.getElementById('packagesGrid');
    elements.packageStatusFilter = document.getElementById('packageStatusFilter');
    elements.refreshPackagesBtn = document.getElementById('refreshPackagesBtn');
    elements.pendingPackagesBadge = document.getElementById('pendingPackagesBadge');
    elements.packageModal = document.getElementById('packageModal');
    elements.packageDetails = document.getElementById('packageDetails');
    elements.packageActions = document.getElementById('packageActions');

    // Widget'lar
    elements.weatherIcon = document.getElementById('weatherIcon');
    elements.weatherTemp = document.getElementById('weatherTemp');
    elements.weatherCity = document.getElementById('weatherCity');
    elements.usdRate = document.getElementById('usdRate');
    elements.eurRate = document.getElementById('eurRate');

    // Kisayol Urunler
    elements.shortcutItems = document.getElementById('shortcutItems');

    // Urun Listesi Modal
    elements.productListModal = document.getElementById('productListModal');
    elements.productSearchInput = document.getElementById('productSearchInput');
    elements.productListItems = document.getElementById('productListItems');
    elements.closeProductListBtn = document.getElementById('closeProductListBtn');

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

    // Kamera ile barkod okuma
    if (elements.cameraScanBtn) {
        elements.cameraScanBtn.addEventListener('click', startBarcodeScanner);
    }
    if (elements.closeScannerBtn) {
        elements.closeScannerBtn.addEventListener('click', stopBarcodeScanner);
    }

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

    // Modal - Tum close butonlari
    document.querySelectorAll('.close-btn, .cancel-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.dataset.modal;
            if (modalId) {
                document.getElementById(modalId).classList.add('hidden');
            }
        });
    });

    // Modal disina tiklandiginda kapat
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.add('hidden');
        });
    });

    // ESC ile modal kapat
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(m => m.classList.add('hidden'));
        }
    });

    // Urun Duzenleme
    if (elements.editProductForm) {
        elements.editProductForm.addEventListener('submit', updateProduct);
    }

    // Kullanici Yonetimi
    if (elements.userForm) {
        elements.userForm.addEventListener('submit', addUser);
    }
    if (elements.editUserForm) {
        elements.editUserForm.addEventListener('submit', updateUser);
    }

    // Paket Siparisler
    if (elements.packageStatusFilter) {
        elements.packageStatusFilter.addEventListener('change', loadPackages);
    }
    if (elements.refreshPackagesBtn) {
        elements.refreshPackagesBtn.addEventListener('click', loadPackages);
    }

    // Barkod alanina cift tikla - urun listesi ac
    if (elements.barcodeInput) {
        elements.barcodeInput.addEventListener('dblclick', openProductListModal);
    }

    // Urun listesi modal
    if (elements.closeProductListBtn) {
        elements.closeProductListBtn.addEventListener('click', closeProductListModal);
    }
    if (elements.productSearchInput) {
        let searchTimeout;
        elements.productSearchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterProductList(elements.productSearchInput.value);
            }, 200);
        });
    }

    // F4 kisayolu - satis tamamla
    document.addEventListener('keydown', (e) => {
        if (e.key === 'F4') {
            e.preventDefault();
            if (!elements.completeSaleBtn.disabled) {
                completeSale();
            }
        }
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

        // Admin-only elementleri goster/gizle
        document.querySelectorAll('.admin-only').forEach(el => {
            if (state.user.role === 'admin') {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });
    }

    // Barkod inputuna focus
    elements.barcodeInput.focus();

    // Verileri yukle
    loadSalesHistory();
    loadPendingPackagesCount();

    // Widget'lari yukle
    loadWeather();
    loadCurrencyRates();

    // Kisayol urunleri yukle
    loadShortcutProducts();

    // Tum urunleri yukle (urun listesi icin)
    loadAllProducts();
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
    if (tabId === 'users') loadUsers();
    if (tabId === 'packages') loadPackages();
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
    state.products = products; // Urunleri sakla

    if (products.length === 0) {
        elements.productsBody.innerHTML = '<tr><td colspan="6" class="empty-message">Urun bulunamadi</td></tr>';
        return;
    }

    let html = '';
    products.forEach(product => {
        const isFav = state.favorites.includes(product.id);
        const favClass = isFav ? 'active' : '';
        html += `
            <tr>
                <td><button class="fav-btn ${favClass}" onclick="toggleFavorite(${product.id})">&#9733;</button></td>
                <td>${escapeHtml(product.barcode)}</td>
                <td>${escapeHtml(product.name)}</td>
                <td>${parseFloat(product.price).toFixed(2)} TL</td>
                <td>${product.stock || 0}</td>
                <td class="action-btns">
                    <button class="edit-btn" onclick="openEditProduct(${product.id})">Duzenle</button>
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
function closeModal(modalId) {
    if (modalId) {
        document.getElementById(modalId).classList.add('hidden');
    } else {
        document.querySelectorAll('.modal').forEach(m => m.classList.add('hidden'));
    }
}

/**
 * Urun duzenleme modalini ac
 */
function openEditProduct(productId) {
    const product = state.products.find(p => p.id === productId);
    if (!product) {
        showNotification('Urun bulunamadi', 'error');
        return;
    }

    elements.editProductId.value = product.id;
    elements.editProductBarcode.value = product.barcode;
    elements.editProductName.value = product.name;
    elements.editProductPrice.value = parseFloat(product.price);
    elements.editProductStock.value = product.stock || 0;

    elements.editProductModal.classList.remove('hidden');
}

/**
 * Urun guncelle
 */
async function updateProduct(e) {
    e.preventDefault();

    const id = parseInt(elements.editProductId.value);
    const barcode = elements.editProductBarcode.value.trim();
    const name = elements.editProductName.value.trim();
    const price = parseFloat(elements.editProductPrice.value);
    const stock = parseInt(elements.editProductStock.value) || 0;

    if (!barcode || !name || isNaN(price)) {
        showNotification('Tum alanlari doldurun!', 'error');
        return;
    }

    try {
        const result = await apiRequest(API.products, 'PUT', { id, barcode, name, price, stock });

        if (result.success) {
            showNotification('Urun guncellendi!');
            elements.editProductModal.classList.add('hidden');
            loadProducts();
        } else {
            showNotification(result.error || 'Urun guncellenemedi', 'error');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Kullanicilari yukle
 */
async function loadUsers() {
    try {
        const result = await apiRequest(`${API.auth}?action=users`);

        if (result.success) {
            state.users = result.data;
            renderUsers(result.data);
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Kullanicilari render et
 */
function renderUsers(users) {
    if (!elements.usersBody) return;

    if (users.length === 0) {
        elements.usersBody.innerHTML = '<tr><td colspan="6" class="empty-message">Kullanici bulunamadi</td></tr>';
        return;
    }

    const roleLabels = { admin: 'Yonetici', cashier: 'Kasiyer' };

    let html = '';
    users.forEach(user => {
        const lastLogin = user.last_login ? new Date(user.last_login).toLocaleString('tr-TR') : '-';
        const statusClass = user.is_active ? 'status-active' : 'status-inactive';
        const statusText = user.is_active ? 'Aktif' : 'Pasif';

        html += `
            <tr>
                <td>${escapeHtml(user.username)}</td>
                <td>${escapeHtml(user.full_name)}</td>
                <td>${roleLabels[user.role] || user.role}</td>
                <td><span class="${statusClass}">${statusText}</span></td>
                <td>${lastLogin}</td>
                <td class="action-btns">
                    <button class="edit-btn" onclick="openEditUser(${user.id})">Duzenle</button>
                    <button class="delete-btn" onclick="deleteUser(${user.id})">Sil</button>
                </td>
            </tr>
        `;
    });

    elements.usersBody.innerHTML = html;
}

/**
 * Yeni kullanici ekle
 */
async function addUser(e) {
    e.preventDefault();

    const username = elements.newUsername.value.trim();
    const full_name = elements.newFullName.value.trim();
    const password = elements.newPassword.value;
    const role = elements.newRole.value;

    if (!username || !full_name || !password) {
        showNotification('Tum alanlari doldurun!', 'error');
        return;
    }

    try {
        const result = await apiRequest(`${API.auth}?action=users`, 'POST', {
            username,
            full_name,
            password,
            role
        });

        if (result.success) {
            showNotification('Kullanici olusturuldu!');
            elements.userForm.reset();
            loadUsers();
        } else {
            showNotification(result.error || 'Kullanici olusturulamadi', 'error');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Kullanici duzenleme modalini ac
 */
function openEditUser(userId) {
    const user = state.users.find(u => u.id === userId);
    if (!user) {
        showNotification('Kullanici bulunamadi', 'error');
        return;
    }

    elements.editUserId.value = user.id;
    elements.editUserFullName.value = user.full_name;
    elements.editUserRole.value = user.role;
    elements.editUserActive.value = user.is_active ? '1' : '0';

    elements.editUserModal.classList.remove('hidden');
}

/**
 * Kullanici guncelle
 */
async function updateUser(e) {
    e.preventDefault();

    const id = parseInt(elements.editUserId.value);
    const full_name = elements.editUserFullName.value.trim();
    const role = elements.editUserRole.value;
    const is_active = elements.editUserActive.value === '1';

    if (!full_name) {
        showNotification('Ad soyad gerekli!', 'error');
        return;
    }

    try {
        const result = await apiRequest(`${API.auth}?action=users`, 'PUT', {
            id,
            full_name,
            role,
            is_active
        });

        if (result.success) {
            showNotification('Kullanici guncellendi!');
            elements.editUserModal.classList.add('hidden');
            loadUsers();
        } else {
            showNotification(result.error || 'Kullanici guncellenemedi', 'error');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Kullanici sil
 */
async function deleteUser(id) {
    if (!confirm('Bu kullaniciyi silmek istediginize emin misiniz?')) return;

    try {
        const result = await apiRequest(`${API.auth}?action=users`, 'DELETE', { id });

        if (result.success) {
            showNotification('Kullanici silindi!');
            loadUsers();
        } else {
            showNotification(result.error || 'Kullanici silinemedi', 'error');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
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

/**
 * Bekleyen paket sayisini yukle
 */
async function loadPendingPackagesCount() {
    if (!elements.pendingPackagesBadge) return;

    try {
        const result = await apiRequest(`${API.orders}?active=1&limit=1`);
        if (result.success && result.data.pending_count > 0) {
            elements.pendingPackagesBadge.textContent = result.data.pending_count;
            elements.pendingPackagesBadge.classList.remove('hidden');
        } else {
            elements.pendingPackagesBadge.classList.add('hidden');
        }
    } catch (error) {
        // Sessizce hata yoksay
        console.log('Paket sayisi yuklenemedi:', error);
    }
}

/**
 * Paket siparisleri yukle
 */
async function loadPackages() {
    if (!elements.packageStatusFilter || !elements.packagesGrid) return;

    const status = elements.packageStatusFilter.value;
    let url = API.orders;

    if (status === 'active') {
        url += '?active=1';
    } else {
        url += `?status=${status}`;
    }

    try {
        const result = await apiRequest(url);

        if (result.success) {
            state.packages = result.data.items;
            renderPackages(result.data.items);

            // Badge guncelle
            if (elements.pendingPackagesBadge) {
                if (result.data.pending_count > 0) {
                    elements.pendingPackagesBadge.textContent = result.data.pending_count;
                    elements.pendingPackagesBadge.classList.remove('hidden');
                } else {
                    elements.pendingPackagesBadge.classList.add('hidden');
                }
            }
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Paket siparislerini render et
 */
function renderPackages(packages) {
    if (!elements.packagesGrid) return;

    if (packages.length === 0) {
        elements.packagesGrid.innerHTML = '<p class="empty-message">Siparis bulunamadi</p>';
        return;
    }

    const statusLabels = {
        pending: 'Bekliyor',
        preparing: 'Hazirlaniyor',
        ready: 'Hazir',
        completed: 'Tamamlandi',
        cancelled: 'Iptal'
    };

    let html = '';
    packages.forEach(pkg => {
        const date = new Date(pkg.created_at).toLocaleString('tr-TR');
        const itemCount = pkg.item_count || '-';

        let actionsHtml = '';
        if (pkg.status === 'pending') {
            actionsHtml = `
                <button class="btn-prepare" onclick="updatePackageStatus(${pkg.id}, 'preparing')">Hazirla</button>
                <button class="btn-cancel" onclick="updatePackageStatus(${pkg.id}, 'cancelled')">Iptal</button>
            `;
        } else if (pkg.status === 'preparing') {
            actionsHtml = `
                <button class="btn-ready" onclick="updatePackageStatus(${pkg.id}, 'ready')">Hazir</button>
                <button class="btn-cancel" onclick="updatePackageStatus(${pkg.id}, 'cancelled')">Iptal</button>
            `;
        } else if (pkg.status === 'ready') {
            actionsHtml = `
                <button class="btn-complete" onclick="updatePackageStatus(${pkg.id}, 'completed')">Tamamla</button>
                <button class="btn-cancel" onclick="updatePackageStatus(${pkg.id}, 'cancelled')">Iptal</button>
            `;
        }

        html += `
            <div class="package-card status-${pkg.status}">
                <div class="package-card-header">
                    <span class="package-order-no">${escapeHtml(pkg.order_no)}</span>
                    <span class="package-status ${pkg.status}">${statusLabels[pkg.status]}</span>
                </div>
                <div class="package-customer">
                    <div class="name">${escapeHtml(pkg.customer_name)}</div>
                    <div class="phone">${escapeHtml(pkg.customer_phone)}</div>
                    <div class="address">${escapeHtml(pkg.customer_address)}</div>
                </div>
                <div class="package-summary">
                    <span class="package-items-count">${itemCount} urun</span>
                    <span class="package-total">${parseFloat(pkg.total_amount).toFixed(2)} TL</span>
                </div>
                <div class="package-time">${date}</div>
                <div class="package-card-actions">
                    <button class="btn-detail" onclick="showPackageDetail(${pkg.id})">Detay</button>
                    ${actionsHtml}
                </div>
            </div>
        `;
    });

    elements.packagesGrid.innerHTML = html;
}

/**
 * Paket detayi goster
 */
async function showPackageDetail(id) {
    try {
        const result = await apiRequest(`${API.orders}?id=${id}`);

        if (!result.success) {
            showNotification(result.error || 'Detay yuklenemedi', 'error');
            return;
        }

        const pkg = result.data;
        const date = new Date(pkg.created_at).toLocaleString('tr-TR');
        const statusLabels = {
            pending: 'Bekliyor',
            preparing: 'Hazirlaniyor',
            ready: 'Hazir',
            completed: 'Tamamlandi',
            cancelled: 'Iptal'
        };

        let html = `
            <div class="package-detail-section">
                <h3>Siparis Bilgileri</h3>
                <div class="package-detail-info">
                    <p><strong>Siparis No:</strong> ${escapeHtml(pkg.order_no)}</p>
                    <p><strong>Durum:</strong> <span class="package-status ${pkg.status}">${statusLabels[pkg.status]}</span></p>
                    <p><strong>Tarih:</strong> ${date}</p>
                    <p><strong>Toplam:</strong> ${parseFloat(pkg.total_amount).toFixed(2)} TL</p>
                </div>
            </div>
            <div class="package-detail-section">
                <h3>Musteri Bilgileri</h3>
                <div class="package-detail-info">
                    <p><strong>Ad Soyad:</strong> ${escapeHtml(pkg.customer_name)}</p>
                    <p><strong>Telefon:</strong> ${escapeHtml(pkg.customer_phone)}</p>
                </div>
                <p style="margin-top:10px;"><strong>Adres:</strong><br>${escapeHtml(pkg.customer_address)}</p>
                ${pkg.customer_note ? `<p style="margin-top:10px;"><strong>Not:</strong> ${escapeHtml(pkg.customer_note)}</p>` : ''}
            </div>
            <div class="package-detail-section">
                <h3>Siparis Kalemleri</h3>
                <table class="package-items-table">
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

        pkg.items.forEach(item => {
            html += `
                <tr>
                    <td>${escapeHtml(item.product_name)}</td>
                    <td>${item.quantity}</td>
                    <td>${parseFloat(item.unit_price).toFixed(2)} TL</td>
                    <td>${parseFloat(item.total_price).toFixed(2)} TL</td>
                </tr>
            `;
        });

        html += '</tbody></table></div>';

        elements.packageDetails.innerHTML = html;

        // Aksiyonlar
        let actionsHtml = '';
        if (pkg.status === 'pending') {
            actionsHtml = `
                <button class="btn-prepare" onclick="updatePackageStatus(${pkg.id}, 'preparing'); closeModal('packageModal');">Hazirlamaya Basla</button>
                <button class="btn-cancel" onclick="updatePackageStatus(${pkg.id}, 'cancelled'); closeModal('packageModal');">Iptal Et</button>
            `;
        } else if (pkg.status === 'preparing') {
            actionsHtml = `
                <button class="btn-ready" onclick="updatePackageStatus(${pkg.id}, 'ready'); closeModal('packageModal');">Hazir Olarak Isaretle</button>
                <button class="btn-cancel" onclick="updatePackageStatus(${pkg.id}, 'cancelled'); closeModal('packageModal');">Iptal Et</button>
            `;
        } else if (pkg.status === 'ready') {
            actionsHtml = `
                <button class="btn-complete" onclick="updatePackageStatus(${pkg.id}, 'completed'); closeModal('packageModal');">Tamamla ve Satisa Ekle</button>
                <button class="btn-cancel" onclick="updatePackageStatus(${pkg.id}, 'cancelled'); closeModal('packageModal');">Iptal Et</button>
            `;
        }

        elements.packageActions.innerHTML = actionsHtml;
        elements.packageModal.classList.remove('hidden');

    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Paket durumunu guncelle
 */
async function updatePackageStatus(id, status) {
    try {
        const result = await apiRequest(API.orders, 'PUT', { id, status });

        if (result.success) {
            if (status === 'completed') {
                showNotification('Siparis tamamlandi ve satisa eklendi!');
            } else if (status === 'cancelled') {
                showNotification('Siparis iptal edildi');
            } else {
                showNotification('Siparis durumu guncellendi');
            }
            loadPackages();
            loadPendingPackagesCount();

            // Satis gecmisini de guncelle
            if (status === 'completed') {
                loadSalesHistory();
            }
        } else {
            showNotification(result.error || 'Islem basarisiz', 'error');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

/**
 * Kamera ile barkod taramayi baslat
 */
async function startBarcodeScanner() {
    if (!elements.barcodeScanner) return;

    // Html5Qrcode kontrolü
    if (typeof Html5Qrcode === 'undefined') {
        showNotification('Kamera destegi yuklenemedi', 'error');
        return;
    }

    // Önce kamera izni iste
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' }
        });
        // İzin alındı, stream'i kapat
        stream.getTracks().forEach(track => track.stop());
    } catch (err) {
        console.error('Kamera izni hatasi:', err);
        showNotification('Kamera izni verilmedi. Ayarlardan izin verin.', 'error');
        return;
    }

    elements.barcodeScanner.classList.remove('hidden');

    state.barcodeScanner = new Html5Qrcode("scannerVideo");

    // Ekran genisligine gore tarama alani
    const screenWidth = window.innerWidth;
    const scanWidth = Math.min(screenWidth - 40, 350);
    const scanHeight = Math.min(150, scanWidth * 0.4);

    const config = {
        fps: 15, // Daha yuksek FPS
        qrbox: { width: scanWidth, height: scanHeight },
        aspectRatio: 1.777778, // 16:9
        formatsToSupport: [
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,
            Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,
            Html5QrcodeSupportedFormats.CODE_128,
            Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.CODE_93,
            Html5QrcodeSupportedFormats.QR_CODE
        ],
        experimentalFeatures: {
            useBarCodeDetectorIfSupported: true // Native API kullan (daha hizli)
        }
    };

    // Yuksek cozunurluk video ayarlari
    const videoConstraints = {
        facingMode: "environment",
        width: { min: 640, ideal: 1280, max: 1920 },
        height: { min: 480, ideal: 720, max: 1080 },
        focusMode: "continuous", // Surekli odaklama
        exposureMode: "continuous"
    };

    try {
        await state.barcodeScanner.start(
            videoConstraints,
            config,
            (decodedText) => {
                // Barkod okundu - titresim ve ses geri bildirimi
                if (navigator.vibrate) {
                    navigator.vibrate(100);
                }
                playBeepSound();
                elements.barcodeInput.value = decodedText;
                stopBarcodeScanner();
                addToCart();
            },
            (errorMessage) => {
                // Tarama hatasi - sessizce yoksay
            }
        );

        // Fener butonunu goster
        showTorchButton();

    } catch (err) {
        console.error('Kamera baslatma hatasi:', err);

        // Basit ayarlarla tekrar dene
        try {
            await state.barcodeScanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 280, height: 120 } },
                (decodedText) => {
                    if (navigator.vibrate) navigator.vibrate(100);
                    playBeepSound();
                    elements.barcodeInput.value = decodedText;
                    stopBarcodeScanner();
                    addToCart();
                },
                (errorMessage) => {}
            );
            showTorchButton();
        } catch (err2) {
            // iOS için ön kamera dene
            try {
                await state.barcodeScanner.start(
                    { facingMode: "user" },
                    { fps: 10, qrbox: { width: 250, height: 100 } },
                    (decodedText) => {
                        if (navigator.vibrate) navigator.vibrate(100);
                        playBeepSound();
                        elements.barcodeInput.value = decodedText;
                        stopBarcodeScanner();
                        addToCart();
                    },
                    (errorMessage) => {}
                );
            } catch (err3) {
                showNotification('Kamera acilamadi. Ayarlardan kamera izni verin.', 'error');
                elements.barcodeScanner.classList.add('hidden');
            }
        }
    }
}

/**
 * Fener butonunu goster
 */
function showTorchButton() {
    const torchBtn = document.getElementById('torchBtn');
    if (torchBtn) {
        torchBtn.classList.remove('hidden');
    }
}

/**
 * Feneri ac/kapat
 */
async function toggleTorch() {
    if (!state.barcodeScanner) return;

    try {
        const track = state.barcodeScanner.getRunningTrackSettings();
        if (track && track.torch !== undefined) {
            const capabilities = await state.barcodeScanner.getRunningTrackCapabilities();
            if (capabilities.torch) {
                state.torchOn = !state.torchOn;
                await state.barcodeScanner.applyVideoConstraints({
                    advanced: [{ torch: state.torchOn }]
                });

                const torchBtn = document.getElementById('torchBtn');
                if (torchBtn) {
                    torchBtn.classList.toggle('active', state.torchOn);
                    torchBtn.innerHTML = state.torchOn ? '&#128294;' : '&#128294;';
                }
            }
        }
    } catch (err) {
        console.log('Fener desteklenmiyor:', err);
    }
}

/**
 * Bip sesi cal
 */
function playBeepSound() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.value = 1800;
        oscillator.type = 'sine';
        gainNode.gain.value = 0.3;

        oscillator.start();
        oscillator.stop(audioContext.currentTime + 0.1);
    } catch (err) {
        // Ses calma desteklenmiyorsa sessizce devam et
    }
}

/**
 * Kamera taramayi durdur
 */
function stopBarcodeScanner() {
    state.torchOn = false;

    if (state.barcodeScanner) {
        state.barcodeScanner.stop().then(() => {
            state.barcodeScanner.clear();
            state.barcodeScanner = null;
        }).catch((err) => {
            console.error('Kamera durdurma hatasi:', err);
        });
    }

    if (elements.barcodeScanner) {
        elements.barcodeScanner.classList.add('hidden');
    }

    // Fener butonunu gizle
    const torchBtn = document.getElementById('torchBtn');
    if (torchBtn) {
        torchBtn.classList.add('hidden');
        torchBtn.classList.remove('active');
    }
}

/**
 * Hava durumunu yukle
 */
async function loadWeather() {
    if (!elements.weatherTemp) return;

    try {
        // IP bazli konum tespiti
        const geoResponse = await fetch('https://ipapi.co/json/');
        const geoData = await geoResponse.json();
        const city = geoData.city || 'Istanbul';
        const lat = geoData.latitude || 41.0082;
        const lon = geoData.longitude || 28.9784;

        // Open-Meteo API (ucretsiz, API key gerektirmez)
        const weatherResponse = await fetch(
            `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`
        );
        const weatherData = await weatherResponse.json();

        if (weatherData.current_weather) {
            const temp = Math.round(weatherData.current_weather.temperature);
            const weatherCode = weatherData.current_weather.weathercode;

            // Hava durumu ikonlari
            let icon = '&#9728;'; // Gunesli
            if (weatherCode >= 61 && weatherCode <= 67) icon = '&#127783;'; // Yagmurlu
            else if (weatherCode >= 71 && weatherCode <= 77) icon = '&#10052;'; // Karli
            else if (weatherCode >= 1 && weatherCode <= 3) icon = '&#9729;'; // Parcali bulutlu
            else if (weatherCode >= 45 && weatherCode <= 48) icon = '&#127787;'; // Sisli

            elements.weatherIcon.innerHTML = icon;
            elements.weatherTemp.textContent = `${temp}°C`;
            elements.weatherCity.textContent = city;
        }
    } catch (error) {
        console.log('Hava durumu yuklenemedi:', error);
        elements.weatherCity.textContent = 'Yuklenmedi';
    }
}

/**
 * Doviz kurlarini yukle
 */
async function loadCurrencyRates() {
    if (!elements.usdRate || !elements.eurRate) return;

    try {
        // Exchangerate API (ucretsiz)
        const response = await fetch('https://api.exchangerate-api.com/v4/latest/TRY');
        const data = await response.json();

        if (data.rates) {
            // TRY bazli oldugu icin ters ceviriyoruz
            const usd = (1 / data.rates.USD).toFixed(2);
            const eur = (1 / data.rates.EUR).toFixed(2);

            elements.usdRate.textContent = `${usd} TL`;
            elements.eurRate.textContent = `${eur} TL`;
        }
    } catch (error) {
        console.log('Doviz kurlari yuklenemedi:', error);
        elements.usdRate.textContent = '--';
        elements.eurRate.textContent = '--';
    }
}

/**
 * Favori urun ekle/cikar
 */
function toggleFavorite(productId) {
    const index = state.favorites.indexOf(productId);

    if (index > -1) {
        state.favorites.splice(index, 1);
        showNotification('Urun kisayollardan cikarildi');
    } else {
        state.favorites.push(productId);
        showNotification('Urun kisayollara eklendi');
    }

    // localStorage'a kaydet
    localStorage.setItem('favorite_products', JSON.stringify(state.favorites));

    // Tabloyu guncelle
    loadProducts();

    // Kisayollari guncelle
    loadShortcutProducts();
}

/**
 * Kisayol urunleri yukle
 */
async function loadShortcutProducts() {
    if (!elements.shortcutItems) return;

    if (state.favorites.length === 0) {
        elements.shortcutItems.innerHTML = '<span class="empty-shortcuts">Favori urun yok</span>';
        return;
    }

    try {
        // Favori urunleri API'den cek
        const result = await apiRequest(`${API.products}?ids=${state.favorites.join(',')}`);

        if (result.success && result.data.items) {
            renderShortcuts(result.data.items);
        } else {
            elements.shortcutItems.innerHTML = '<span class="empty-shortcuts">Favori urun yok</span>';
        }
    } catch (error) {
        console.log('Kisayol urunleri yuklenemedi:', error);
        elements.shortcutItems.innerHTML = '<span class="empty-shortcuts">Yuklenemedi</span>';
    }
}

/**
 * Kisayollari render et
 */
function renderShortcuts(products) {
    if (!elements.shortcutItems) return;

    if (products.length === 0) {
        elements.shortcutItems.innerHTML = '<span class="empty-shortcuts">Favori urun yok</span>';
        return;
    }

    let html = '';
    products.forEach(product => {
        html += `
            <div class="shortcut-item" onclick="addShortcutToCart('${product.barcode}')">
                <span class="shortcut-name">${escapeHtml(product.name)}</span>
                <span class="shortcut-price">${parseFloat(product.price).toFixed(2)} TL</span>
            </div>
        `;
    });

    elements.shortcutItems.innerHTML = html;
}

/**
 * Kisayol urununu sepete ekle
 */
async function addShortcutToCart(barcode) {
    elements.barcodeInput.value = barcode;
    await addToCart();
}

/**
 * Tum urunleri yukle (urun listesi icin)
 */
async function loadAllProducts() {
    try {
        const result = await apiRequest(`${API.products}?limit=1000`);
        if (result.success) {
            state.allProducts = result.data.items;
        }
    } catch (error) {
        console.log('Urunler yuklenemedi:', error);
    }
}

/**
 * Urun listesi modalini ac
 */
function openProductListModal() {
    if (!elements.productListModal) return;

    elements.productListModal.classList.remove('hidden');
    elements.productSearchInput.value = '';
    elements.productSearchInput.focus();

    // Urunleri listele
    renderProductList(state.allProducts);
}

/**
 * Urun listesi modalini kapat
 */
function closeProductListModal() {
    if (elements.productListModal) {
        elements.productListModal.classList.add('hidden');
    }
    elements.barcodeInput.focus();
}

/**
 * Urun listesini filtrele
 */
function filterProductList(search) {
    if (!search) {
        renderProductList(state.allProducts);
        return;
    }

    const searchLower = search.toLowerCase();
    const filtered = state.allProducts.filter(p =>
        p.name.toLowerCase().includes(searchLower) ||
        p.barcode.toLowerCase().includes(searchLower)
    );

    renderProductList(filtered);
}

/**
 * Urun listesini render et
 */
function renderProductList(products) {
    if (!elements.productListItems) return;

    if (!products || products.length === 0) {
        elements.productListItems.innerHTML = '<p class="product-list-empty">Urun bulunamadi</p>';
        return;
    }

    let html = '';
    products.slice(0, 50).forEach(product => {
        html += `
            <div class="product-list-item" onclick="selectProductFromList('${product.barcode}')">
                <div class="product-info-text">
                    <div class="name">${escapeHtml(product.name)}</div>
                    <div class="barcode">${escapeHtml(product.barcode)}</div>
                </div>
                <div class="price">${parseFloat(product.price).toFixed(2)} TL</div>
            </div>
        `;
    });

    elements.productListItems.innerHTML = html;
}

/**
 * Urun listesinden urun sec
 */
async function selectProductFromList(barcode) {
    closeProductListModal();
    elements.barcodeInput.value = barcode;
    await addToCart();
}

// Global fonksiyonlar (onclick icin)
window.updateQuantity = updateQuantity;
window.removeFromCart = removeFromCart;
window.deleteProduct = deleteProduct;
window.showSaleDetail = showSaleDetail;
window.openEditProduct = openEditProduct;
window.openEditUser = openEditUser;
window.deleteUser = deleteUser;
window.showPackageDetail = showPackageDetail;
window.updatePackageStatus = updatePackageStatus;
window.closeModal = closeModal;
window.startBarcodeScanner = startBarcodeScanner;
window.stopBarcodeScanner = stopBarcodeScanner;
window.toggleTorch = toggleTorch;
window.toggleFavorite = toggleFavorite;
window.addShortcutToCart = addShortcutToCart;
window.selectProductFromList = selectProductFromList;
