// Market Satış Sistemi JavaScript

// API URL'leri
const API = {
    products: 'api/products.php',
    sales: 'api/sales.php'
};

// Sepet
let cart = [];

// DOM Elemanları
const elements = {
    // Tab
    tabBtns: document.querySelectorAll('.tab-btn'),
    tabContents: document.querySelectorAll('.tab-content'),

    // Satış
    barcodeInput: document.getElementById('barcodeInput'),
    addToCartBtn: document.getElementById('addToCartBtn'),
    productInfo: document.getElementById('productInfo'),
    foundProductName: document.getElementById('foundProductName'),
    foundProductPrice: document.getElementById('foundProductPrice'),
    cartItems: document.getElementById('cartItems'),
    cartTotal: document.getElementById('cartTotal'),
    completeSaleBtn: document.getElementById('completeSaleBtn'),

    // Ürün
    productForm: document.getElementById('productForm'),
    productBarcode: document.getElementById('productBarcode'),
    productName: document.getElementById('productName'),
    productPrice: document.getElementById('productPrice'),
    productsBody: document.getElementById('productsBody'),

    // Geçmiş
    salesBody: document.getElementById('salesBody'),

    // Modal
    saleModal: document.getElementById('saleModal'),
    saleDetails: document.getElementById('saleDetails'),
    closeBtn: document.querySelector('.close-btn'),

    // Bildirim
    notification: document.getElementById('notification')
};

// Tab Değiştirme
elements.tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        elements.tabBtns.forEach(b => b.classList.remove('active'));
        elements.tabContents.forEach(c => c.classList.remove('active'));

        btn.classList.add('active');
        const tabId = btn.dataset.tab;
        document.getElementById(tabId).classList.add('active');

        // Tab'a göre veri yükle
        if (tabId === 'products') loadProducts();
        if (tabId === 'history') loadSalesHistory();
        if (tabId === 'sales') elements.barcodeInput.focus();
    });
});

// Bildirim Göster
function showNotification(message, type = 'success') {
    elements.notification.textContent = message;
    elements.notification.className = `notification ${type}`;
    elements.notification.classList.remove('hidden');

    setTimeout(() => {
        elements.notification.classList.add('hidden');
    }, 3000);
}

// API İstekleri
async function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    if (data) {
        options.body = JSON.stringify(data);
    }

    const response = await fetch(url, options);
    const result = await response.json();

    if (!response.ok) {
        throw new Error(result.error || 'Bir hata oluştu');
    }

    return result;
}

// Barkod ile Ürün Ara
async function searchProduct(barcode) {
    try {
        const product = await apiRequest(`${API.products}?barcode=${encodeURIComponent(barcode)}`);
        return product;
    } catch (error) {
        return null;
    }
}

// Sepete Ekle
async function addToCart() {
    const barcode = elements.barcodeInput.value.trim();
    if (!barcode) return;

    const product = await searchProduct(barcode);

    if (!product) {
        showNotification('Ürün bulunamadı!', 'error');
        elements.productInfo.classList.add('hidden');
        return;
    }

    // Ürün bilgisini göster
    elements.foundProductName.textContent = product.name;
    elements.foundProductPrice.textContent = `${parseFloat(product.price).toFixed(2)} TL`;
    elements.productInfo.classList.remove('hidden');

    // Sepette var mı kontrol et
    const existingItem = cart.find(item => item.product_id === product.id);

    if (existingItem) {
        existingItem.quantity++;
        existingItem.total_price = existingItem.quantity * existingItem.unit_price;
    } else {
        cart.push({
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

// Sepeti Render Et
function renderCart() {
    if (cart.length === 0) {
        elements.cartItems.innerHTML = '<p class="empty-cart">Sepet boş</p>';
        elements.cartTotal.textContent = '0.00 TL';
        elements.completeSaleBtn.disabled = true;
        return;
    }

    let html = '';
    let total = 0;

    cart.forEach((item, index) => {
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
                <button class="cart-item-qty remove-item" onclick="removeFromCart(${index})">X</button>
            </div>
        `;
    });

    elements.cartItems.innerHTML = html;
    elements.cartTotal.textContent = `${total.toFixed(2)} TL`;
    elements.completeSaleBtn.disabled = false;
}

// Miktar Güncelle
function updateQuantity(index, change) {
    cart[index].quantity += change;

    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    } else {
        cart[index].total_price = cart[index].quantity * cart[index].unit_price;
    }

    renderCart();
}

// Sepetten Çıkar
function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

// Satışı Tamamla
async function completeSale() {
    if (cart.length === 0) return;

    try {
        const result = await apiRequest(API.sales, 'POST', { items: cart });
        showNotification(`Satış tamamlandı! Toplam: ${result.total_amount.toFixed(2)} TL`);

        // Sepeti temizle
        cart = [];
        renderCart();
        elements.productInfo.classList.add('hidden');
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Ürünleri Yükle
async function loadProducts() {
    try {
        const products = await apiRequest(API.products);
        renderProducts(products);
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Ürünleri Render Et
function renderProducts(products) {
    if (products.length === 0) {
        elements.productsBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Henüz ürün eklenmemiş</td></tr>';
        return;
    }

    let html = '';
    products.forEach(product => {
        html += `
            <tr>
                <td>${escapeHtml(product.barcode)}</td>
                <td>${escapeHtml(product.name)}</td>
                <td>${parseFloat(product.price).toFixed(2)} TL</td>
                <td><button class="delete-btn" onclick="deleteProduct(${product.id})">Sil</button></td>
            </tr>
        `;
    });

    elements.productsBody.innerHTML = html;
}

// Ürün Ekle
async function addProduct(e) {
    e.preventDefault();

    const barcode = elements.productBarcode.value.trim();
    const name = elements.productName.value.trim();
    const price = parseFloat(elements.productPrice.value);

    if (!barcode || !name || isNaN(price)) {
        showNotification('Tüm alanları doldurun!', 'error');
        return;
    }

    try {
        await apiRequest(API.products, 'POST', { barcode, name, price });
        showNotification('Ürün başarıyla eklendi!');
        elements.productForm.reset();
        loadProducts();
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Ürün Sil
async function deleteProduct(id) {
    if (!confirm('Bu ürünü silmek istediğinize emin misiniz?')) return;

    try {
        await apiRequest(API.products, 'DELETE', { id });
        showNotification('Ürün silindi!');
        loadProducts();
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Satış Geçmişini Yükle
async function loadSalesHistory() {
    try {
        const sales = await apiRequest(API.sales);
        renderSalesHistory(sales);
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Satış Geçmişini Render Et
function renderSalesHistory(sales) {
    if (sales.length === 0) {
        elements.salesBody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Henüz satış yapılmamış</td></tr>';
        return;
    }

    let html = '';
    sales.forEach(sale => {
        const date = new Date(sale.created_at).toLocaleString('tr-TR');
        html += `
            <tr>
                <td>#${sale.id}</td>
                <td>${date}</td>
                <td>${parseFloat(sale.total_amount).toFixed(2)} TL</td>
                <td><button class="detail-btn" onclick="showSaleDetail(${sale.id})">Detay</button></td>
            </tr>
        `;
    });

    elements.salesBody.innerHTML = html;
}

// Satış Detayı Göster
async function showSaleDetail(id) {
    try {
        const sale = await apiRequest(`${API.sales}?id=${id}`);
        const date = new Date(sale.created_at).toLocaleString('tr-TR');

        let html = `
            <p><strong>Satış No:</strong> #${sale.id}</p>
            <p><strong>Tarih:</strong> ${date}</p>
            <p><strong>Toplam:</strong> ${parseFloat(sale.total_amount).toFixed(2)} TL</p>
            <table>
                <thead>
                    <tr>
                        <th>Ürün</th>
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

// HTML Escape
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Event Listeners
elements.addToCartBtn.addEventListener('click', addToCart);
elements.barcodeInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') addToCart();
});
elements.completeSaleBtn.addEventListener('click', completeSale);
elements.productForm.addEventListener('submit', addProduct);
elements.closeBtn.addEventListener('click', () => {
    elements.saleModal.classList.add('hidden');
});
elements.saleModal.addEventListener('click', (e) => {
    if (e.target === elements.saleModal) {
        elements.saleModal.classList.add('hidden');
    }
});

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', () => {
    elements.barcodeInput.focus();
});
