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

                <!-- Standort-Karte (Google Maps) -->
                <div class="footer-map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2695.8739482469127!2d8.283099!3d47.284600!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x479070e7f7f7f7f7%3A0x1234567890!2sSteinbrunnengasse%203A%2C%205707%20Seengen!5e0!3m2!1sde!2sch!4v1234567890" width="100%" height="300" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
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