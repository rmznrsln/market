<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Satis Sistemi</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Market Satis Sistemi</h1>
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
                        <input type="text" id="barcodeInput" placeholder="Barkod okutun veya girin..." autofocus>
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
                    <button id="completeSaleBtn" class="complete-btn" disabled>Satisi Tamamla</button>
                </div>
            </div>
        </section>

        <section id="products" class="tab-content">
            <div class="product-form">
                <h2>Yeni Urun Ekle</h2>
                <form id="productForm">
                    <div class="form-group">
                        <label for="productBarcode">Barkod</label>
                        <input type="text" id="productBarcode" required>
                    </div>
                    <div class="form-group">
                        <label for="productName">Urun Adi</label>
                        <input type="text" id="productName" required>
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Fiyat (TL)</label>
                        <input type="number" id="productPrice" step="0.01" min="0" required>
                    </div>
                    <button type="submit" class="submit-btn">Urun Ekle</button>
                </form>
            </div>

            <div class="product-list">
                <h2>Kayitli Urunler</h2>
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th>Barkod</th>
                            <th>Urun Adi</th>
                            <th>Fiyat</th>
                            <th>Islem</th>
                        </tr>
                    </thead>
                    <tbody id="productsBody">
                    </tbody>
                </table>
            </div>
        </section>

        <section id="history" class="tab-content">
            <h2>Satis Gecmisi</h2>
            <table id="salesTable">
                <thead>
                    <tr>
                        <th>Satis No</th>
                        <th>Tarih</th>
                        <th>Toplam</th>
                        <th>Detay</th>
                    </tr>
                </thead>
                <tbody id="salesBody">
                </tbody>
            </table>
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

    <script src="assets/app.js"></script>
</body>
</html>
