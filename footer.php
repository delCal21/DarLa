            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">&copy; <?= date('Y') ?> DARLa HRIS</div>
                    <div>
                        <a href="privacy_policy.php" class="text-decoration-none">Privacy Policy</a>
                        &middot;
                        <a href="terms_conditions.php" class="text-decoration-none">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
<script src="startbootstrap-sb-admin-gh-pages/js/scripts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add more educational background entries
    document.querySelector('.add-education')?.addEventListener('click', function() {
        const container = document.querySelector('.education-container');
        const newEntry = document.createElement('div');
        newEntry.className = 'education-entry mb-3';
        newEntry.innerHTML = `
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Level</label>
                    <select class="form-select education-level" name="edu_level[]">
                        <option value="">Select Level</option>
                        <option value="ELEMENTARY">Elementary</option>
                        <option value="HIGH SCHOOL">High School</option>
                        <option value="VOCATIONAL">Vocational</option>
                        <option value="COLLEGE">College</option>
                        <option value="GRADUATE STUDIES">Graduate Studies</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="form-label">Name of School (Write in full)</label>
                    <input type="text" class="form-control education-school" name="edu_school_name[]" placeholder="Enter full school name">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Basic Education/Degree/Course (Write in full)</label>
                    <input type="text" class="form-control education-course" name="edu_degree_course[]" placeholder="e.g. Primary Education, Bachelor of Science in Computer Science">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Period of Attendance - From</label>
                    <input type="text" class="form-control education-period-from" name="edu_period_from[]" placeholder="e.g. 1973">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Period of Attendance - To</label>
                    <input type="text" class="form-control education-period-to" name="edu_period_to[]" placeholder="e.g. 1979">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Highest Level/Units Earned<br><small class="text-muted">(if not graduated)</small></label>
                    <input type="text" class="form-control education-highest" name="edu_highest_level_units[]" placeholder="e.g. N/A, 2nd Year">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year Graduated</label>
                    <input type="text" class="form-control education-graduated" name="edu_year_graduated[]" placeholder="e.g. 1979">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Scholarship/Academic Honors Received</label>
                    <input type="text" class="form-control education-honors" name="edu_scholarship_honors[]" placeholder="e.g. Salutatorian, Dean's Lister">
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-education">
                        <i class="fas fa-times me-1"></i> Remove Education Entry
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newEntry);

        // Add event listener to the remove button for the new entry
        newEntry.querySelector('.remove-education')?.addEventListener('click', function() {
            this.closest('.education-entry').remove();
        });
    });

    // Add event listeners for remove buttons to existing entries
    document.querySelectorAll('.remove-education').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.education-entry').remove();
        });
    });
});
</script>
</body>
</html>

