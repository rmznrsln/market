<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Satis Sistemi</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Login Ekrani -->
    <div id="loginScreen" class="login-screen">
        <div class="login-container">
            <div class="login-header">
                <h1>Market Satis Sistemi</h1>
                <p>Lutfen giris yapin</p>
            </div>
            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="loginUsername">Kullanici Adi</label>
                    <input type="text" id="loginUsername" placeholder="Kullanici adinizi girin" required autofocus>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Sifre</label>
                    <input type="password" id="loginPassword" placeholder="Sifrenizi girin" required>
                </div>
                <button type="submit" class="login-btn" id="loginBtn">
                    <span class="btn-text">Giris Yap</span>
                    <span class="btn-loading hidden">Yukleniyor...</span>
                </button>
                <div id="loginError" class="login-error hidden"></div>
            </form>
        </div>
    </div>

    <!-- Ana Uygulama -->
    <div id="mainApp" class="container hidden">
        <header>
            <div class="header-top">
                <h1>Market Satis Sistemi</h1>
                <div class="user-info">
                    <span id="currentUserName">-</span>
                    <span id="currentUserRole" class="user-role">-</span>
                    <button id="logoutBtn" class="logout-btn">Cikis</button>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <span class="stat-label">Bugun</span>
                    <span id="todaySalesCount" class="stat-value">0</span>
                    <span class="stat-suffix">satis</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Toplam</span>
                    <span id="todaySalesAmount" class="stat-value">0.00</span>
                    <span class="stat-suffix">TL</span>
                </div>
            </div>
            <nav>
                <button class="tab-btn active" data-tab="sales">Satis</button>
                <button class="tab-btn" data-tab="products">Urun Yonetimi</button>
                <button class="tab-btn" data-tab="history">Satis Gecmisi</button>
            </nav>
        </header>

        <section id="sales" class="tab-content active">
            <div class="sales-container">
                <div class="barcode-section">
                    <h2>Barkod Okut</h2>
                    <div class="input-group">
                        <input type="text" id="barcodeInput" placeholder="Barkod okutun veya girin...">
                        <button id="addToCartBtn">Ekle</button>
                    </div>
                    <div id="productInfo" class="product-info hidden">
                        <span id="foundProductName"></span>
                        <span id="foundProductPrice"></span>
                    </div>
                </div>

                <div class="cart-section">
                    <h2>Sepet</h2>
                    <div class="cart-items" id="cartItems">
                        <p class="empty-cart">Sepet bos</p>
                    </div>
                    <div class="cart-total">
                        <span>Toplam:</span>
                        <span id="cartTotal">0.00 TL</span>
                    </div>
                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod" value="cash" checked>
                            <span>Nakit</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="paymentMethod" value="card">
                            <span>Kart</span>
                        </label>
                    </div>
                    <button id="completeSaleBtn" class="complete-btn" disabled>Satisi Tamamla</button>
                </div>
            </div>
        </section>

        <section id="products" class="tab-content">
            <div class="product-form">
                <h2>Yeni Urun Ekle</h2>
                <form id="productForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productBarcode">Barkod</label>
                            <input type="text" id="productBarcode" required>
                        </div>
                        <div class="form-group">
                            <label for="productName">Urun Adi</label>
                            <input type="text" id="productName" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productPrice">Fiyat (TL)</label>
                            <input type="number" id="productPrice" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="productStock">Stok</label>
                            <input type="number" id="productStock" min="0" value="0">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Urun Ekle</button>
                </form>
            </div>

            <div class="product-list">
                <div class="list-header">
                    <h2>Kayitli Urunler</h2>
                    <div class="search-box">
                        <input type="text" id="productSearch" placeholder="Urun ara...">
                    </div>
                </div>
                <div class="table-container">
                    <table id="productsTable">
                        <thead>
                            <tr>
                                <th>Barkod</th>
                                <th>Urun Adi</th>
                                <th>Fiyat</th>
                                <th>Stok</th>
                                <th>Islem</th>
                            </tr>
                        </thead>
                        <tbody id="productsBody">
                        </tbody>
                    </table>
                </div>
                <div id="productsPagination" class="pagination"></div>
            </div>
        </section>

        <section id="history" class="tab-content">
            <div class="history-header">
                <h2>Satis Gecmisi</h2>
                <div class="history-filters">
                    <input type="date" id="dateFrom" class="date-filter">
                    <span>-</span>
                    <input type="date" id="dateTo" class="date-filter">
                    <button id="filterSalesBtn" class="filter-btn">Filtrele</button>
                </div>
            </div>
            <div class="table-container">
                <table id="salesTable">
                    <thead>
                        <tr>
                            <th>Satis No</th>
                            <th>Tarih</th>
                            <th>Kasiyer</th>
                            <th>Odeme</th>
                            <th>Toplam</th>
                            <th>Detay</th>
                        </tr>
                    </thead>
                    <tbody id="salesBody">
                    </tbody>
                </table>
            </div>
            <div id="salesPagination" class="pagination"></div>
        </section>

        <div id="saleModal" class="modal hidden">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h2>Satis Detayi</h2>
                <div id="saleDetails"></div>
            </div>
        </div>

        <div id="notification" class="notification hidden"></div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay hidden">
        <div class="loading-spinner"></div>
    </div>

    <script src="assets/app.js"></script>
</body>
</html>
