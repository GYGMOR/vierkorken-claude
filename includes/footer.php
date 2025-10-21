<?php
// includes/footer.php - Footer mit Standort
?>

    </main>

    <footer class="footer">
        <div class="footer-content">
            <!-- MAIN SECTION - Standort + Maps -->
            <div class="footer-main">
                <!-- Kontakt Info (Links) -->
                <div class="footer-section">
                    <h4>Standort</h4>
                    <p>
                        <strong>Vier Korken Wein Boutique</strong><br>
                        Steinbrunnengasse 3A<br>
                        5707 Seengen<br>
                        Tel: <a href="tel:+41623900404">062 390 04 04</a>
                    </p>
                    <p style="margin-top: 1rem;">
                        <a href="https://www.google.com/maps/search/Steinbrunnengasse+3A,+5707+Seengen" target="_blank" class="map-link">Google Maps</a><br>
                        <a href="https://maps.apple.com/?address=Steinbrunnengasse+3A,+5707+Seengen" target="_blank" class="map-link">Apple Maps</a>
                    </p>
                </div>

                <!-- Standort-Karte (Google Maps Static) -->
                <div class="footer-map-container">
                    <a href="https://www.google.com/maps/dir/?api=1&destination=Steinbrunnengasse+3A,5707+Seengen,Schweiz" target="_blank" class="map-link-wrapper" style="display: block; position: relative; cursor: pointer;">
                        <img
                            src="https://maps.googleapis.com/maps/api/staticmap?center=47.32175,8.28325&zoom=15&size=600x300&markers=color:red%7C47.32175,8.28325&key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8"
                            alt="Standort Karte"
                            style="width: 100%; height: 300px; border-radius: 8px; object-fit: cover;"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <div class="map-fallback" style="display: none; width: 100%; height: 300px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; align-items: center; justify-content: center; flex-direction: column; color: white; text-align: center;">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="margin-bottom: 1rem;">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <h3 style="margin: 0 0 0.5rem 0; font-size: 1.5rem;">Unser Standort</h3>
                            <p style="margin: 0; font-size: 1rem;">Steinbrunnengasse 3A<br>5707 Seengen</p>
                            <p style="margin-top: 1rem; padding: 0.8rem 1.5rem; background: rgba(255,255,255,0.2); border-radius: 25px; font-weight: 600;">Route anzeigen →</p>
                        </div>
                        <div class="map-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0); border-radius: 8px; display: flex; align-items: center; justify-content: center; opacity: 0; transition: all 0.3s;">
                            <span style="background: rgba(76, 37, 76, 0.95); color: white; padding: 1rem 2rem; border-radius: 25px; font-weight: 600; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">Route anzeigen →</span>
                        </div>
                    </a>
                </div>

                <style>
                .map-link-wrapper:hover .map-overlay {
                    opacity: 1 !important;
                    background: rgba(0,0,0,0.2) !important;
                }
                </style>
            </div>

            <!-- BOTTOM GRID - Öffnungszeiten & Links -->
            <div class="footer-bottom-grid">
                <div class="footer-section">
                    <h4>Öffnungszeiten</h4>
                    <p>
                        Mo: Geschlossen<br>
                        Di: Geschlossen<br>
                        <strong>Mi: 13:30 - 18:30 Uhr</strong><br>
                        <strong>Do: 13:30 - 18:30 Uhr</strong><br>
                        <strong>Fr: 09:00 - 12:00 Uhr</strong><br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;13:30 - 18:30 Uhr<br>
                        <strong>Sa: 09:00 - 14:00 Uhr</strong><br>
                        So: Geschlossen
                    </p>
                </div>

                <div class="footer-section">
                    <h4>Follow Us</h4>
                    <p>
                        <a href="https://www.instagram.com/vier.korken.wein.boutique/" target="_blank">Instagram</a><br>
                        <a href="https://www.facebook.com/Vier-Korken-Wein-Boutique/" target="_blank">Facebook</a>
                    </p>
                </div>

                <div class="footer-section">
                    <h4>Service</h4>
                    <ul>
                        <li><a href="?page=shop">Shop</a></li>
                        <li><a href="?page=contact">Kontakt</a></li>
                        <li><a href="?page=impressum">Impressum</a></li>
                        <li><a href="?page=agb">AGB</a></li>
                        <li><a href="?page=datenschutz">Datenschutz</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- COPYRIGHT -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Vier Korken. Alle Rechte vorbehalten.</p>
        </div>
    </footer>

    <style>
    .footer {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 3rem 0 0;
        border-top: 3px solid var(--accent-gold);
        margin-top: auto;
        width: 100%;
    }

    .footer-content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    /* MAIN SECTION - Maps */
    .footer-main {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        padding: 3rem 0 2rem;
    }

    .footer-map-container {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    /* BOTTOM GRID - Öffnungszeiten & Links */
    .footer-bottom-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .footer-section h4 {
        color: var(--accent-gold);
        font-size: 1.1rem;
        margin-top: 0;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .footer-section p {
        font-size: 0.9rem;
        line-height: 1.8;
        margin: 0;
        color: white;
        opacity: 0.95;
    }

    .footer-section a {
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .footer-section a:hover {
        color: var(--accent-gold);
        text-decoration: underline;
    }

    .map-link {
        display: inline-block;
        margin-top: 0.5rem;
    }

    .footer-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-section ul li {
        margin-bottom: 0.5rem;
    }

    .footer-section ul li a {
        display: inline-block;
    }

    /* COPYRIGHT */
    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        padding: 2rem 0;
        text-align: center;
        background: rgba(0, 0, 0, 0.1);
        width: 100%;
    }

    .footer-bottom p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.8;
    }

    /* RESPONSIVE */
    @media (max-width: 1024px) {
        .footer-main {
            grid-template-columns: 1fr;
            padding: 2rem 0 1rem;
        }

        .footer-map-container iframe {
            height: 250px;
        }
    }

    @media (max-width: 768px) {
        .footer-main {
            grid-template-columns: 1fr;
            gap: 1.5rem;
            padding: 2rem 0 1rem;
        }

        .footer-bottom-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .footer-map-container iframe {
            height: 280px;
        }

        .footer-section h4 {
            font-size: 1rem;
        }

        .footer-section p {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .footer-content {
            padding: 0 1rem;
        }

        .footer-main {
            padding: 1.5rem 0 1rem;
        }

        .footer-bottom-grid {
            grid-template-columns: 1fr;
            padding-bottom: 1rem;
        }

        .footer-map-container iframe {
            height: 250px;
        }

        .footer-section h4 {
            font-size: 0.95rem;
            margin-bottom: 0.8rem;
        }

        .footer-section p {
            font-size: 0.8rem;
        }

        .footer-bottom {
            padding: 1rem 0;
        }

        .footer-bottom p {
            font-size: 0.8rem;
        }
    }
    </style>

  </body>
</html>