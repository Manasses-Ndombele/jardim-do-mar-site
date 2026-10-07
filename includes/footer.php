<?php
// Shared footer — include at bottom of every page
// Usage: include_once __DIR__ . '/includes/footer.php';
?>

<!-- ═══════════════════════ FOOTER ═══════════════════════ -->
<footer class="site-footer">
    <div class="container">

        <div class="footer-logo-wrap">
            <div class="footer-logo-icon">
                <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="20" cy="20" r="18" fill="none" stroke="white" stroke-width="1.5"/>
                    <path d="M20 8 L20 32 M12 16 L28 16" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M8 28 Q20 22 32 28" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="footer-brand-text">
                <span>ASSEMBLEIA CRISTÃ</span>
                <span>JARDIM DO MAR</span>
            </div>
        </div>

        <div class="footer-grid">

            <div class="footer-col">
                <h4>Redes Sociais</h4>
                <ul>
                    <li>
                        <a href="https://www.facebook.com/jardin.domar" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                            Facebook
                        </a>
                    </li>
                    <li>
                        <a href="https://www.youtube.com/@AssembleiaCristaJardimdoMar." target="_blank" rel="noopener noreferrer" aria-label="Youtube">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 0 0 1.46 6.42 29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="white"/></svg>
                            Youtube
                        </a>
                    </li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Mapa do Site</h4>
                <ul>
                    <li><a href="<?= $rootPath ?? '' ?>doutrina/index.php">Doutrina</a></li>
                    <li><a href="<?= $rootPath ?? '' ?>index.php#contatos">Localização</a></li>
                    <li><a href="<?= $rootPath ?? '' ?>multimedia/pregacoes.php">Pregações</a></li>
                    <li><a href="<?= $rootPath ?? '' ?>sobre.php">História</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Endereço</h4>
                <address class="footer-address">
                    Município do Cazenga,<br>
                    Bairro São João (Cuca)<br>
                    Em frente à fábrica de Cuca,<br>
                    Na rua da fábrica de panelas,<br>
                    Rua Jardim do Mar<br>
                    Luanda, Angola
                </address>
            </div>

        </div><!-- .footer-grid -->

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Assembleia Cristã Jardim do Mar &middot; Luanda, Angola &middot; Todos os direitos reservados</p>
        </div>

    </div>
</footer>

<!-- Global JS -->
<script src="<?= $assetsPath ?? '' ?>assets/js/global.js"></script>
</body>
</html>
