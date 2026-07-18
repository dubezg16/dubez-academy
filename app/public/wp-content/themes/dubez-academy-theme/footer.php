<footer class="footer">
    <div class="footer-inner">

        <div class="footer-brand">
            <h3>Dubez Academy</h3>
            <p>
                A disciplined institution committed to measurable excellence,
                structured evaluation, and competitive academic leadership.
            </p>
        </div>

        <div class="footer-links">

            <div class="footer-column">
                <h4>Academics</h4>
                <a href="#">Curriculum</a>
                <a href="#">Departments</a>
                <a href="#">Examinations</a>
            </div>

            <div class="footer-column">
                <h4>Admissions</h4>
                <a href="#">Apply Now</a>
                <a href="#">Requirements</a>
                <a href="#">Scholarships</a>
            </div>

            <div class="footer-column">
                <h4>Contact</h4>
                <p>Email: info@dubezacademy.edu</p>
                <p>Phone: +000 000 0000</p>
            </div>

        </div>

    </div>

    <div class="footer-bottom">
        © <?php echo date('Y'); ?> Dubez Academy. All Rights Reserved.
    </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggle = document.getElementById("navToggle");
    const menu = document.getElementById("navMenu");

    if (toggle && menu) {
        toggle.addEventListener("click", function() {
            menu.classList.toggle("show");
        });
    }
});
</script>

<!-- Cursor Glow -->
<script>
document.addEventListener("DOMContentLoaded", function() {

    const glow = document.createElement("div");
    glow.classList.add("cursor-glow");
    document.body.appendChild(glow);

    document.addEventListener("mousemove", function(e) {
        glow.style.left = e.clientX + "px";
        glow.style.top = e.clientY + "px";
    });

});
</script>

<?php wp_footer(); ?>
</body>
</html>