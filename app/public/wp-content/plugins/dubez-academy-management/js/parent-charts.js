document.addEventListener("DOMContentLoaded", function () {

    if (typeof dubezParentAnalytics === "undefined") {
        return;
    }

    const navy = "#0b1f3a";
    const gold = "#c6a756";
    const gray = "#e5e5e5";

    // Term Trend Chart
    const termCtx = document.getElementById("termTrendChart");

    if (termCtx && Object.keys(dubezParentAnalytics.termTrend).length > 0) {

        new Chart(termCtx, {
            type: "line",
            data: {
                labels: Object.keys(dubezParentAnalytics.termTrend),
                datasets: [{
                    data: Object.values(dubezParentAnalytics.termTrend),
                    borderColor: navy,
                    backgroundColor: "rgba(11, 31, 58, 0.05)",
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: gold
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100 },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Subject Chart
    const subjectCtx = document.getElementById("subjectTrendChart");

    if (subjectCtx && dubezParentAnalytics.subjectTrend.length > 0) {

        const labels = dubezParentAnalytics.subjectTrend.map(s => s.subject_name);
        const values = dubezParentAnalytics.subjectTrend.map(s => s.average);

        new Chart(subjectCtx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: navy
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100 },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Attendance Chart
    const attendanceCtx = document.getElementById("attendanceTrendChart");

    if (attendanceCtx && Object.keys(dubezParentAnalytics.attendanceTrend).length > 0) {

        new Chart(attendanceCtx, {
            type: "line",
            data: {
                labels: Object.keys(dubezParentAnalytics.attendanceTrend),
                datasets: [{
                    data: Object.values(dubezParentAnalytics.attendanceTrend),
                    borderColor: gold,
                    backgroundColor: "rgba(198, 167, 86, 0.1)",
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100 },
                    x: { grid: { display: false } }
                }
            }
        });
    }

});