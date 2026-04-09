            </main>

            <footer class="site-footer">
                <div class="container footer-grid">
                    <div>
                        <h3>TripTally</h3>
                        <p>Plan smarter trips with budgets, itineraries, and packing progress in one dashboard.</p>
                    </div>
                    <div>
                        <h4>Quick Links</h4>
                        <a href="<?= h(url_for('/planner.php')) ?>">Trip Planner</a>
                        <a href="<?= h(url_for('/budget.php')) ?>">Budget Board</a>
                        <a href="<?= h(url_for('/packing.php')) ?>">Packing Checklist</a>
                    </div>
                    <div>
                        <h4>Capstone Coverage</h4>
                        <p>HTML, CSS, JavaScript, PHP sessions/cookies, MySQL CRUD, responsive layouts, and deployment-ready structure.</p>
                    </div>
                </div>
            </footer>
        </div>

        <script src="<?= h(url_for('/assets/js/app.js')) ?>"></script>
    </body>
</html>
