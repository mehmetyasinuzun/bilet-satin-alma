<footer class="bg-dark text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><i class="bi bi-bus-front-fill"></i> Bilet Platformu</h5>
                <p class="text-muted">Modern, güvenli ve hızlı otobüs bileti satın alma platformu.</p>
            </div>
            <div class="col-md-4">
                <h6>Hızlı Linkler</h6>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-white-50">Sefer Ara</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="my_tickets.php" class="text-white-50">Biletlerim</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <a href="https://sibervatan.org" target="_blank" rel="noopener noreferrer" class="siber-vatan-logo">
                    <img src="assets/images/sibervatanlogo.svg" alt="Siber Vatan" style="height: 70px; margin-top: 5px;">
                </a>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center text-muted">
            <small>&copy; <?= date('Y') ?> Bilet Platformu. Tüm hakları saklıdır.</small>
        </div>
    </div>
</footer>

<style>
.siber-vatan-logo {
    display: inline-block;
    transition: transform 0.3s ease;
}

.siber-vatan-logo img {
    transition: filter 0.3s ease;
    cursor: pointer;
}

.siber-vatan-logo:hover {
    transform: scale(1.3);
}

.siber-vatan-logo:hover img {
    filter: invert(.27) sepia(.98) saturate(74.61) hue-rotate(-2deg) contrast(1.19);
}
</style>
