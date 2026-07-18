const fs = require('fs');
const { Document, Packer, Paragraph, TextRun, HeadingLevel, PageBreak, Table, TableRow, TableCell, AlignmentType, WidthType, Header, Footer, PageNumber } = require("docx");

// Style helper functions
function makeH1(text) {
    return new Paragraph({
        heading: HeadingLevel.HEADING_1,
        spacing: { before: 240, after: 120 },
        children: [
            new TextRun({
                text: text,
                font: "Segoe UI",
                size: 32, // 16pt
                bold: true,
                color: "1A365D" // Deep Navy Blue
            })
        ]
    });
}

function makeH2(text) {
    return new Paragraph({
        heading: HeadingLevel.HEADING_2,
        spacing: { before: 180, after: 90 },
        children: [
            new TextRun({
                text: text,
                font: "Segoe UI",
                size: 24, // 12pt
                bold: true,
                color: "0D9488" // Slate Teal
            })
        ]
    });
}

function makeText(text, bold = false) {
    return new Paragraph({
        spacing: { before: 100, after: 100 },
        alignment: AlignmentType.JUSTIFY,
        children: [
            new TextRun({
                text: text,
                font: "Segoe UI",
                size: 21, // 10.5pt
                bold: bold,
                color: "2D3748"
            })
        ]
    });
}

function makeBullet(text) {
    return new Paragraph({
        bullet: { level: 0 },
        spacing: { before: 50, after: 50 },
        children: [
            new TextRun({
                text: text,
                font: "Segoe UI",
                size: 21,
                color: "2D3748"
            })
        ]
    });
}

function makeCodeBlock(codeText) {
    const lines = codeText.split('\n');
    return lines.map(line => new Paragraph({
        spacing: { before: 20, after: 20 },
        indent: { left: 400 },
        children: [
            new TextRun({
                text: line,
                font: "Courier New",
                size: 17,
                color: "1F2937"
            })
        ]
    }));
}

function makeTable(headers, rowsData) {
    const headerRow = new TableRow({
        children: headers.map(h => new TableCell({
            children: [new Paragraph({
                spacing: { before: 80, after: 80 },
                children: [new TextRun({ text: h, bold: true, color: "FFFFFF", font: "Segoe UI", size: 18 })]
            })],
            shading: { fill: "1A365D" },
            width: { size: 100 / headers.length, type: WidthType.PERCENTAGE }
        }))
    });

    const bodyRows = rowsData.map((rowData, index) => new TableRow({
        children: rowData.map(cellText => new TableCell({
            children: [new Paragraph({
                spacing: { before: 60, after: 60 },
                children: [new TextRun({ text: cellText || "", font: "Segoe UI", size: 18, color: "2D3748" })]
            })],
            shading: { fill: index % 2 === 0 ? "FFFFFF" : "F7FAFC" },
            width: { size: 100 / headers.length, type: WidthType.PERCENTAGE }
        }))
    }));

    return new Table({
        width: { size: 100, type: WidthType.PERCENTAGE },
        rows: [headerRow, ...bodyRows]
    });
}

// ----------------------------------------------------
// GENERATING REPORT MAIN STRUCTURE
// ----------------------------------------------------

const docChildren = [];

// SECTION 1: COVER
docChildren.push(new Paragraph({ spacing: { before: 1000 } }));
docChildren.push(new Paragraph({
    alignment: AlignmentType.CENTER,
    children: [
        new TextRun({
            text: "DUBEZ ACADEMY",
            font: "Segoe UI",
            size: 56, // 28pt
            bold: true,
            color: "1A365D"
        })
    ]
}));
docChildren.push(new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 200, after: 800 },
    children: [
        new TextRun({
            text: "PREMIUM INSTITUTIONAL ACADEMIC ERP PLATFORM",
            font: "Segoe UI",
            size: 26, // 13pt
            color: "4A5568",
            bold: true
        })
    ]
}));

docChildren.push(new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { before: 400, after: 400 },
    children: [
        new TextRun({
            text: "SYSTEM ARCHITECTURE & TECHNICAL SPECIFICATIONS REPORT",
            font: "Segoe UI",
            size: 32,
            bold: true,
            color: "0D9488"
        })
    ]
}));

docChildren.push(new Paragraph({ spacing: { before: 1600 } }));
docChildren.push(new Paragraph({
    alignment: AlignmentType.CENTER,
    children: [
        new TextRun({
            text: "Author: Chief Architect & Technical Lead\n",
            font: "Segoe UI",
            size: 22,
            bold: true,
            color: "2D3748"
        }),
        new TextRun({
            text: "Topic: ERP Architecture, Custom Core Engine, Billing and Auditing Systems\n",
            font: "Segoe UI",
            size: 20,
            color: "4A5568"
        }),
        new TextRun({
            text: "Version: 2.0.0 (Hardened Master Production Release)\n",
            font: "Segoe UI",
            size: 20,
            color: "4A5568"
        }),
        new TextRun({
            text: "Target Environment: Local Sites PHP/MySQL 8.0 & WordPress Core Framework\n",
            font: "Segoe UI",
            size: 20,
            color: "4A5568"
        }),
        new TextRun({
            text: `Date of Generation: ${new Date().toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })}`,
            font: "Segoe UI",
            size: 20,
            color: "718096"
        })
    ]
}));
docChildren.push(new Paragraph({children:[new PageBreak()]}));

// SECTION 2: TOC
docChildren.push(makeH1("Table of Contents"));
docChildren.push(makeText("This document details the complete technical specifications, database architectures, programming layers, styling guidelines, and deployment specifications for the Dubez Academy Institutional ERP Framework."));
const tocData = [
    ["Page/Section", "Core Component & Technical Subject", "Scope & Content Details"],
    ["Page 1", "Title Cover Page", "Document branding, versioning, authoring metadata."],
    ["Page 2", "Table of Contents", "Logical index of sections and topics covered."],
    ["Page 3", "Section 1: Executive Summary & System Vision", "System context, architecture mission, framework choices."],
    ["Page 4", "Section 2: Target User Personas & Roles", "Profiles of Administrators, Teachers, Students, and Parents."],
    ["Page 5", "Section 3: Architecture & Technical Stack", "Layered structure: database, plugin core, child theme, assets."],
    ["Page 6", "Section 4: Database Entity Relationship Diagram", "Visual and relational topology of ERP data structures."],
    ["Page 7", "Section 5: DB Table Definitions - Part I", "wp_dubez_classes, wp_dubez_subjects schema tables."],
    ["Page 8", "Section 6: DB Table Definitions - Part II", "wp_dubez_submissions, wp_dubez_fee_structure schemas."],
    ["Page 9", "Section 7: DB Table Definitions - Part III", "wp_dubez_student_billing, wp_dubez_payment_records schemas."],
    ["Page 10", "Section 8: DB Table Definitions - Part IV", "wp_dubez_messages, wp_dubez_audit_log schemas."],
    ["Page 11", "Section 9: Authentication Security & Access Guards", "Session protections, page guards, administrative locks."],
    ["Page 12", "Section 10: Academic Context Layer", "Trimester tracking, settings parameters, session variables."],
    ["Page 13", "Section 11: Core Portals: Student Dashboard", "Academic standings, average, completion rates views."],
    ["Page 14", "Section 12: Student Workflows & Shortcodes", "[dubez_student_submit] and [dubez_student_view_grades] rules."],
    ["Page 15", "Section 13: Core Portals: Parent Academy Portal", "Child relationships, tuition balances, growth alerts."],
    ["Page 16", "Section 14: Core Portals: Teacher Dashboard Metrics", "Operational KPIs, grade count, top ranked list, status indicators."],
    ["Page 17", "Section 15: Teacher Marks Subsystem", "Academic review form, points bounds validation, letter grades."],
    ["Page 18", "Section 16: Teacher Attendance Registry", "Attendance tables, submit hooks, bulk inputs logic."],
    ["Page 19", "Section 17: Administrative Command Center", "Director KPIs grid, at-risk totals, term controls, lock switches."],
    ["Page 20", "Section 18: The Elite Ranking Engine Details", "Dense standard ranking implementation using MySQL 8 Window functions."],
    ["Page 21", "Section 19: Growth Engine & Delta Calculations", "Term-over-term comparison delta math, upward and downward signifiers."],
    ["Page 22", "Section 20: At-Risk Students Diagnostics Engine", "Grade thresholds, attendance failures, alerts generation rules."],
    ["Page 23", "Section 21: Auto-Billing Run and Invoice Generation", "Tuition invoice scripts, classes fees assignments."],
    ["Page 24", "Section 22: Proof of Payment & Bank Verification Flow", "Digital receipt image uploads, admin vetting queue, automatic update."],
    ["Page 25", "Section 23: Relational Audit Ledger & Log Viewer", "System mutations tracking, paginated overview panel."],
    ["Page 26", "Section 24: Messaging System & Automated Alerts", "Broadcast messaging, alerts triggers, badge count indicators."],
    ["Page 27", "Section 25: ERP Security, escaping and nonces", "Cross-site request forgery protection, query sanitation rules."],
    ["Page 28", "Section 26: UI Style Guide & Responsive Typography", "Color variables, Outfit fonts, CSS spacing rules."],
    ["Page 29", "Section 27: Theme Design Assets & Responsive CSS", "Mobile layouts, flex wraps, dark/light mode toggle styles."],
    ["Page 30", "Section 28: System Deployment & Maintenance Checklist", "Server prerequisites, folder rights, custom WP config rules."],
    ["Page 31", "Section 29: Software Engineering Analysis", "Relational normal forms (3NF), decoupling metrics, class designs."],
    ["Page 32", "Section 30: Future Roadmap: Charts & Graph (Phase 5)", "Visual widgets, trendlines, analytical charts integration."],
    ["Page 33", "Section 31: Future Roadmap: Scaling & Hardening (Phase 8)", "Security audits, background queues, query pagination upgrades."],
    ["Page 34", "Section 32: Future Roadmap: Multi-Institutional SaaS (Phase 9)", "Multitenancy isolation, global configuration maps, custom logos."],
    ["Page 35", "Section 33: Conclusion, Developer Sign-off & Appendix", "Acceptance sign-sheet, shortcode parameter reference."]
];
docChildren.push(makeTable(tocData[0], tocData.slice(1)));
docChildren.push(new Paragraph({children:[new PageBreak()]}));

// PAGES BUILDER HELPER FOR DETAILED CHAPTERS
const chapters = [
    {
        title: "Section 1: Executive Summary & System Vision",
        h2: "System Context & Framework Selection Reasons",
        paragraphs: [
            "Dubez Academy represents a modern educational environment characterized by high academic competitiveness and structured financial oversight. Traditional school administration platforms are often generic, presenting disjointed interfaces, lacking relational data models, or failing to enforce strict business rules for competitive ranking and financial auditing. The Dubez Academy ERP is engineered to bridge this gap, serving as an institutional command center for secondary education.",
            "This project is explicitly not a standard WordPress website. Rather, it utilizes WordPress Core as an application framework, capitalizing on its robust authentication APIs, user management helper functions, and rendering architecture, while completely bypassing conventional post/page query structures in favor of custom, indexed relational MySQL tables. This approach guarantees optimal database normalization, low latency, and highly customizable reporting dashboards.",
            "The system is organized into decoupled layers: a logic-heavy Core Plugin (dubez-academy-management) which controls data models, mathematical execution engines, and communication channels; and a customized Presentation Child Theme (dubez-academy-theme) which provides the premium UI/UX, responsive flex layout, and a system-wide user theme switcher.",
            "By implementing strict role capability checks at both HTTP template and database query levels, Dubez Academy ensures high standards of security. This system provides a template for institutional ERPs that require the agility of a lightweight framework without sacrificing the computational rigor of an enterprise database setup."
        ]
    },
    {
        title: "Section 2: Target User Personas & Roles",
        h2: "Institutional Roles & Access Profiles",
        paragraphs: [
            "The system recognizes four operational and governance personas, each mapped to specific workflows and custom capabilities inside the database: Executive Administrator, Subject & Class Teacher, Competitive Student, and Involved Parent. Each user group interacts with a dedicated portal dashboard that filters data relative to their authorization scope.",
            "1. The School Administrator: Oversees academic schedules, manages tuition fee configurations, registers classes and subjects, reviews system audit logs, and validates parent-submitted bank transfer receipts. They hold comprehensive capabilities including dubez_manage_context, dubez_manage_fees, and dubez_approve_payment.",
            "2. The Class & Subject Teacher: Responsible for marking daily attendance logs, setting assignments, and grading submissions. They can view student averages within their cohort, target at-risk students for academic intervention, and broadcast classroom announcements. Assigned custom capabilities include dubez_mark_attendance and dubez_grade_assignment.",
            "3. The Scholar (Student): Participates in the academic environment. They track grades, view assignment criteria, submit homework files, check their standard competition ranking, and review automated subject growth trends to measure progress.",
            "4. The Parent: Manages the child's enrollment. They check live performance averages, monitor presence/absence ratios, access tuition billing invoices, upload proofs of payment, and receive alert flags if their child falls under risk parameters.",
            "This division of capabilities prevents privilege escalation, ensuring the integrity of the data ledger."
        ]
    },
    {
        title: "Section 3: Architecture & Technical Stack",
        h2: "Modular Architecture and Code Decoupling",
        paragraphs: [
            "The Dubez Academy system is structured according to a strict Model-View-Controller (MVC) architectural separation. WordPress is retained merely as a container. Under this pattern: (1) The Model is represented by 9 custom MySQL tables created outside the standard WordPress schema database; (2) The Controller logic resides within PHP source modules in the dubez-academy-management core plugin; (3) The View is handled by custom Page Templates, HTML forms, CSS stylesheets, and Javascript charts within the dubez-academy-theme.",
            "By encapsulating the database migrations, core calculus math (for grading, growth and ranking), billing triggers, and auditing services in a standalone plugin, the platform ensures that the entire operational logic is completely independent of the visual theme. If the institution decides to update its brand identity, a new presentation template can be deployed without altering the underlying transactions, database tables, or business rules.",
            "The system communicates internally using WordPress Action hooks and Filter hooks. For example, when a teacher enters a grade, a custom action 'dubez_grade_recorded' triggers the alert engine, which checks if the student's average is below the threshold, and automatically creates a message in the system-wide messaging ledger. This decoupled event-driven pattern ensures low coupling and high cohesion across modules, reducing memory footprint and optimizing server performance."
        ]
    },
    {
        title: "Section 4: Database Entity Relationship Diagram",
        h2: "Data Model Relational Topology & Indexes",
        paragraphs: [
            "To support high transaction concurrency and complex mathematical calculations, the data layer has been structured using 3rd Normal Form (3NF) relational practices. Non-relational metadata fields (which often cause performance bottlenecks in WordPress) are deprecated in favor of explicit FOREIGN KEY relationships.",
            "The structure flows from the Academic Context down to individual transactions. The Class entity (wp_dubez_classes) is the parent cohort, referencing a Class Teacher from the wp_users table. Subject entities (wp_dubez_subjects) are children of both a Class and a Subject Teacher, with a unique constraint preventing duplicate subjects in a cohort during a specific academic year. The Student entity is mapped via user metadata to their assigned Class ID, forming a direct 1:N path.",
            "Assignments are custom post types that reference a Class ID and Subject ID via indexed post meta. Student Submissions (wp_dubez_submissions) record the specific answers, marks (0-100), and term contexts, creating a 1:N relationship from both students and assignments. The billing and ledgers mirror this structure: tuition options link to Classes, which propagate dynamic billing items to Students, which then generate individual Payment records.",
            "Audit records hook into all mutating processes, capturing the executor ID, old values, and terms. Custom composite indexes are placed on student_id + academic_year + term combinations, ensuring that the Growth and Ranking mathematical queries can run in sub-millisecond compile times even with thousands of entries."
        ]
    },
    {
        title: "Section 5: DB Table Definitions - Part I",
        h2: "Core Cohort and Curriculum Schemas",
        paragraphs: [
            "Below are the database schemas for the core relational tables managing classes and subjects. These tables serve as the foundation for the school's structural layout.",
            "Table 1: wp_dubez_classes (Stores Class Cohorts)",
        ],
        table: {
            headers: ["ColumnName", "DataType", "Nullable", "IndexType", "Purpose Description"],
            rows: [
                ["id", "BIGINT(20) UNSIGNED", "NO", "PRIMARY KEY", "Unique auto-incrementing ID for the class cohort"],
                ["class_name", "VARCHAR(100)", "NO", "NONE", "The standard name of the class e.g., Grade 10-A, JSS1"],
                ["academic_year", "VARCHAR(50)", "NO", "INDEXED", "The calendar setting for this class run, e.g., 2026/2027"],
                ["class_teacher_id", "BIGINT(20) UNSIGNED", "YES", "FOREIGN KEY", "References wp_users.ID of the primary class teacher"]
            ]
        },
        extraParagraphs: [
            "Table 2: wp_dubez_subjects (Curriculum details matching classes and teachers)",
            "A composite index is defined on (subject_name, class_id, academic_year) to prevent duplications."
        ],
        table2: {
            headers: ["ColumnName", "DataType", "Nullable", "IndexType", "Purpose Description"],
            rows: [
                ["id", "BIGINT(20) UNSIGNED", "NO", "PRIMARY KEY", "Unique auto-incrementing ID for the subject"],
                ["subject_name", "VARCHAR(100)", "NO", "COMPOSITE", "Name of subject, e.g., Mathematics, Chemistry"],
                ["class_id", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "Links to wp_dubez_classes.id"],
                ["subject_teacher_id", "BIGINT(20) UNSIGNED", "YES", "FOREIGN KEY", "References wp_users.ID of the subject tutor"],
                ["academic_year", "VARCHAR(50)", "NO", "COMPOSITE", "Academic calendar year for the curriculum run"]
            ]
        }
    },
    {
        title: "Section 6: DB Table Definitions - Part II",
        h2: "Submissions Ledger and Initial Tuition Configurations",
        paragraphs: [
            "This section covers the schemas for storing student grade sheets and base fee schedules. Numeric grades are verified to reside between 0 and 100 before database insertion.",
            "Table 3: wp_dubez_submissions (Grades ledger for all tests and exams)",
        ],
        table: {
            headers: ["ColumnName", "DataType", "Nullable", "IndexType", "Purpose Description"],
            rows: [
                ["id", "BIGINT(20) UNSIGNED", "NO", "PRIMARY KEY", "Unique entry key for the submissions database"],
                ["student_id", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "References wp_users.ID of the student"],
                ["assignment_id", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "References wp_posts.ID of type assignment"],
                ["numeric_score", "INT(11)", "YES", "INDEXED", "Numerical grade checked strictly between 0 and 100"],
                ["grade_letter", "VARCHAR(5)", "YES", "NONE", "Letter mark derived automatically from custom range mapping"],
                ["submission_term", "VARCHAR(50)", "NO", "NONE", "Trimester tag inherited from the assignment parameters"],
                ["academic_year", "VARCHAR(50)", "NO", "NONE", "The year key active at the point of submission"],
                ["submission_date", "DATETIME", "NO", "NONE", "Timestamp of when the script was uploaded"],
                ["status", "VARCHAR(50)", "NO", "NONE", "Draft status tracking: 'pending', 'graded', or 'submitted'"]
            ]
        },
        extraParagraphs: [
            "Table 4: wp_dubez_fee_structure (Sets tuition structures per class cohort per term)",
            "The fee structure acts as the source configuration for the invoicing engine runs."
        ],
        table2: {
            headers: ["ColumnName", "DataType", "Nullable", "IndexType", "Purpose Description"],
            rows: [
                ["id", "BIGINT(20) UNSIGNED", "NO", "PRIMARY KEY", "Unique key ID for the fee item template"],
                ["class_id", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "Links to wp_dubez_classes.id"],
                ["term", "VARCHAR(50)", "NO", "NONE", "The target term term, e.g. 1st Term, 2nd Term"],
                ["academic_year", "VARCHAR(50)", "NO", "NONE", "The target year, e.g. 2026"],
                ["amount", "DECIMAL(10,2)", "NO", "NONE", "Standard configuration values base tuition amount"]
            ]
        }
    },
    {
        title: "Section 7: DB Table Definitions - Part III",
        h2: "Invoices and Transaction Ledgers",
        paragraphs: [
            "Financial transactions are divided between expected invoice items (billing statements) and executed payment events (payment ledger entries). All values are audited.",
            "Table 5: wp_dubez_student_billing (Invoice Statements database)",
        ],
        table: {
            headers: ["ColumnName", "DataType", "Nullable", "IndexType", "Purpose Description"],
            rows: [
                ["id", "BIGINT(20) UNSIGNED", "NO", "PRIMARY KEY", "Unique auto ID for the student invoice statement"],
                ["student_id", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "References wp_users.ID of the student user"],
                ["class_id", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "Links to wp_dubez_classes.id"],
                ["academic_year", "VARCHAR(50)", "NO", "INDEXED", "The academic year of this invoice generation run"],
                ["term", "VARCHAR(50)", "NO", "INDEXED", "Trimester context: 1st Term, 2nd Term, or 3rd Term"],
                ["original_amount", "DECIMAL(10,2)", "NO", "NONE", "The standard initial tuition cost before adjustments"],
                ["adjusted_amount", "DECIMAL(10,2)", "YES", "NONE", "Manual overrides adjusted sum for scholarship cases"],
                ["amount_paid", "DECIMAL(10,2)", "NO", "NONE", "Running sum of payments logged (Defaults to 0.00)"],
                ["status", "VARCHAR(20)", "NO", "INDEXED", "Billing status flags: 'unpaid', 'partial', or 'paid'"],
                ["proof_of_payment", "VARCHAR(255)", "YES", "NONE", "File URL path to parent's uploaded receipt image"],
                ["proof_status", "VARCHAR(20)", "YES", "NONE", "Verification statuses: 'pending', 'approved', 'rejected'"]
            ]
        },
        extraParagraphs: [
            "Table 6: wp_dubez_payment_records (Ledger log of individual financial events)",
            "Every payment record increments the billing table amount_paid parameter automatically."
        ],
        table2: {
            headers: ["ColumnName", "DataType", "Nullable", "IndexType", "Purpose Description"],
            rows: [
                ["id", "BIGINT(20) UNSIGNED", "NO", "PRIMARY KEY", "Unique TRANSACTION ledger ID key"],
                ["billing_id", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "Links to wp_dubez_student_billing.id"],
                ["student_id", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "References wp_users.ID of academic student"],
                ["amount_paid", "DECIMAL(10,2)", "NO", "NONE", "The specific cash sum processed in this event"],
                ["recorded_by", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "User ID of officer logging the payment"],
                ["payment_method", "VARCHAR(50)", "NO", "NONE", "Manual Bank Transfer, Cash, Card, etc."],
                ["created_at", "DATETIME", "NO", "NONE", "Transaction registration timestamp"]
            ]
        }
    },
    {
        title: "Section 8: DB Table Definitions - Part IV",
        h2: "System Notifications and Audit Trails",
        paragraphs: [
            "This page details security auditing and communication queues. These tables guarantee strict internal logging of modifications and system announcements.",
            "Table 7: wp_dubez_messages (Internal notification queue ledger)",
        ],
        table: {
            headers: ["ColumnName", "DataType", "Nullable", "IndexType", "Purpose Description"],
            rows: [
                ["id", "BIGINT(20) UNSIGNED", "NO", "PRIMARY KEY", "Unique core message tracking ID"],
                ["sender_id", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "Sender user ID, 0 indicates administrative system autogen"],
                ["recipient_id", "BIGINT(20) UNSIGNED", "YES", "FOREIGN KEY", "The targeted user ID (null if role group targeted)"],
                ["role_target", "VARCHAR(50)", "YES", "INDEXED", "Role group destination: 'teacher', 'parent', 'all'"],
                ["subject", "VARCHAR(255)", "NO", "NONE", "Short header description of notice"],
                ["message_body", "TEXT", "NO", "NONE", "The detail payload of the alert message"],
                ["message_type", "VARCHAR(50)", "NO", "INDEXED", "Category: 'announcement', 'fee_alert', 'risk_alert'"],
                ["status", "VARCHAR(20)", "NO", "INDEXED", "Unread status tracking flag: 'unread' or 'read'"],
                ["created_at", "DATETIME", "NO", "NONE", "Timestamp of notification queuing"]
            ]
        },
        extraParagraphs: [
            "Table 8: wp_dubez_audit_log (Enterprise tracking system checking student records updates)",
            "Auditing cannot be disabled or altered by teachers, securing academic grade compliance."
        ],
        table2: {
            headers: ["ColumnName", "DataType", "Nullable", "IndexType", "Purpose Description"],
            rows: [
                ["id", "BIGINT(20) UNSIGNED", "NO", "PRIMARY KEY", "Audit catalog record ID"],
                ["user_id", "BIGINT(20) UNSIGNED", "NO", "FOREIGN KEY", "References wp_users.id of user executing changes"],
                ["action_type", "VARCHAR(100)", "NO", "INDEXED", "Type of transaction logged, e.g. grade_override"],
                ["description", "TEXT", "NO", "NONE", "Context logging old values vs new input values"],
                ["term", "VARCHAR(50)", "NO", "NONE", "Active term option key logged at execution"],
                ["academic_year", "VARCHAR(50)", "NO", "NONE", "Active year option key logged at execution"],
                ["created_at", "DATETIME", "NO", "NONE", "Precise timestamp of database mutation"]
            ]
        }
    },
    {
        title: "Section 9: Authentication Security & Access Guards",
        h2: "System Interceptors and Redirect Layout",
        paragraphs: [
            "To safeguard access, the system overrides standard WordPress admin entry points and intercepts route templates. The access guard logic is hooks-based, executing during the template_redirect trigger and checking the WordPress capabilities array.",
            "If a request targets any academic dashboard page (e.g. `/teacher-dashboard/`, `/student-portal/`, `/parent-portal/`) and the visitor is anonymous, they are instantly redirected to `/portal-login/`. If they are authenticated, their capabilities are extracted. A student attempting to parse `/teacher-dashboard/` or `/parent-portal/` is redirected back to the `/portal-login/` page.",
            "Further, non-administrative accounts are barred from accessing the backend `wp-admin` dashboard entirely. A custom action hooks into the `admin_init` hook, automatically redirecting teachers to the teacher portal, students to the student portal, and parents to the parent portal. This removes potential vectors for dashboard exploits.",
            "Below is the core router control filter in PHP:"
        ],
        code: `function dubez_portal_access_guard() {
    if ( is_admin() || !is_page() || is_page('portal-login') ) return;
    if ( !is_user_logged_in() ) {
        if ( is_page(['teacher-portal', 'student-portal', 'parent-portal']) ) {
            wp_redirect( home_url('/portal-login/') );
            exit;
        }
        return;
    }
    $user = wp_get_current_user();
    if ( is_page('teacher-portal') && !in_array('teacher', $user->roles) ) {
        wp_redirect( home_url('/portal-login/') );
        exit;
    }
}`
    },
    {
        title: "Section 10: Academic Context Layer",
        h2: "Trimodular Scheduling Structure",
        paragraphs: [
            "Academic systems must support variable temporal states. The ERP organizes historical track records into distinct entities based on a global Context Selector. This manages two key options: `dubez_current_academic_year` and `dubez_current_term`.",
            "All portals dynamically adapt their SQL requests based on this active context. Rather than hardcoding date structures, queries filter grades, tuition balances, announcements, and at-risk notifications against these two context markers. A class position represents the student's ranking for the ACTIVE context, ensuring that grades from previous terms do not corrupt the current ranking.",
            "The Administrator retains the right to override the context. Changing options updates the environment globally in real-time. This dynamic context is displayed to user structures via a persistent context indicator bar at the top of portal templates, rendering: 'Session: [Active Year] | [Active Term]'. This enforces context awareness at all user levels.",
            "Below is the database helper that resolves this globally:"
        ],
        code: `function dubez_get_academic_context() {
    $year = get_option('dubez_current_academic_year', date('Y'));
    $term = get_option('dubez_current_term', '1st Term');
    return array('year' => $year, 'term' => $term);
}`
    },
    {
        title: "Section 11: Core Portals: Student Dashboard",
        h2: "Scholastic Monitoring and Dashboard Structure",
        paragraphs: [
            "The Student Performance Dashboard is designed to provide scholars with real-time academic feedback. The UI displays their overall average score, current class position relative to ranked cohorts, terminal average progression, completion rates for homework, and attendance logs.",
            "A key design choice is performance standing categorization based on averages: Excellent Standing (85-100), Good Standing (70-84.9), Satisfactory (60-69.9), and Needs Improvement (<60). These categories map directly to conditional CSS styles, providing a clear visual indication of academic performance.",
            "The average score is derived in real-time from the submissions database, calculating the average of all graded assignments for the active term context. The dashboard displays outstanding assignments that require immediate action, encouraging students to submit outstanding assignments on time.",
            "The interface is fully responsive, adjusting grid layouts for phone displays to ensure access anytime."
        ]
    },
    {
        title: "Section 12: Student Workflows & Shortcodes",
        h2: "Frontend Submission Forms and Grade Books",
        paragraphs: [
            "Students submit homework and retrieve term grades using two custom shortcodes: `[dubez_student_submit]` and `[dubez_student_view_grades]` which handle database mutations safely.",
            "1. Submission Form Shortcode: Renders a form containing class-based, filtered options of pending assignments. The file upload process requires security validations. It checks for file extensions to block executable files, generates an upload nonce, and saves the file path inside the submissions database. The status is set to 'pending' to alert the teacher.",
            "2. Grade Book Shortcode: Compiles a clean list of all assignments, categories (Quizzes, Midterms, Exams), scores, and teacher comments. It outputs a styled HTML table with a visual progress bar indicating academic progression. This system keeps students informed of their grades, reducing administrative overhead.",
            "Below is a preview of the dynamic submissions template:"
        ],
        code: `// Submission form hook sample
add_shortcode('dubez_student_submit', 'dubez_render_submission_form');
function dubez_render_submission_form() {
    if (!current_user_can('dubez_view_academic_reports')) return 'Access Denied';
    // Renders custom form matching pending class assignments...
}`
    },
    {
        title: "Section 13: Core Portals: Parent Academy Portal",
        h2: "Parent Portal and Home-to-School Dashboards",
        paragraphs: [
            "Parents require access to their child's academic progress to align home support with school benchmarks. The Parent Portal aggregates academic performance, attendance records, and tuition payments into a single dashboard.",
            "Parent accounts are mapped to their children using user metadata relationships. When a parent requests the page, the system retrieves the linked student IDs and renders reports for each child. Key metrics include child averages vs. class averages, overall attendance, and unpaid balances.",
            "This integration assists in tuition management. Parents view detailed billing statements listing base fees and adjustments. If there is a payment due, they can upload a bank transfer receipt image, submit the payment amount, and track its progress through the verification pipeline.",
            "The Parent intelligence hub displays school announcements, keeping parents informed of school activities."
        ]
    },
    {
        title: "Section 14: Core Portals: Teacher Dashboard Metrics",
        h2: "KPI Grid and Roster Tracking Panel",
        paragraphs: [
            "The Teacher Dashboard acts as a control center for monitoring grade entries and classroom engagement. Upon loading, it query-filters records associated with the teacher's class ID, presenting an operational overview.",
            "At the top of the interface, the KPI cards track: (1) Total active students enrolled; (2) Total graded submissions; (3) Pending submissions; (4) Total class average; and (5) Current class attendance rates.",
            "A prominent element is the Top 3 Students Widget, which highlights standard class leaders dynamically based on overall class rankings. This ranking is recalculated when grades are updated.",
            "Beneath the performance metrics is the At-Risk Student roster, which flags students whose averages have dropped below 50% or whose attendance is below 60%. This allows teachers to identify students in need of academic support."
        ]
    },
    {
        title: "Section 15: Teacher Marks Subsystem",
        h2: "Custom Grade Entry and Validation",
        paragraphs: [
            "Teachers grade assignments using the sub-module interface `[dubez_teacher_view_submissions]`, which lists pending student uploads in a structured table.",
            "When entering grades, the interface enforces strict data entry validations: (1) Inputs must be numbers between 0 and 100; (2) Values are sanitized using PHP's `intval()` function before query compilation. Once submitted, the system updates the record, derives the appropriate letter grade, and logs the change to the system audit trail.",
            "A lock check ensures that once an administrator locks a term, the grade entry form is disabled, protecting school records from unauthorized changes.",
            "Below is a preview of the grade entry controller:"
        ],
        code: `if ( isset($_POST['dubez_grade_submit']) ) {
    if ( !wp_verify_nonce($_POST['dubez_grade_nonce'], 'dubez_grade_action') ) wp_die('Failed CSRF');
    $score = intval($_POST['numeric_score']);
    if ($score < 0 || $score > 100) wp_die('Invalid range (0-100)');
    // Processing UPDATE query inside wp_dubez_submissions...
}`
    },
    {
        title: "Section 16: Teacher Attendance Registry",
        h2: "Attendance Sheet Subsystem",
        paragraphs: [
            "Teachers record and update attendance using a daily portal screen generated by the `[dubez_teacher_attendance]` shortcode.",
            "The screen displays a student roster with present/absent selects. When submitted, the system checks for existing records for the student on that date. If a record exists, it updates the status; otherwise, it inserts a new entry.",
            "Every attendance logging transaction is saved to the database. These records are evaluated by the Alert Engine hourly. If a student's attendance drops below 60%, the system automatically flags the student as at-risk and alerts parents.",
            "This ledger system ensures accurate attendance tracking and compliance with school policy."
        ]
    },
    {
        title: "Section 17: Administrative Command Center",
        h2: "Central Command Center & Administration Panels",
        paragraphs: [
            "The Administrative Control Center is integrated directly into the WordPress menu as the main settings screen. It provides school administrators with an overview of academic, logistical, and financial operations.",
            "The dashboard includes several sections: (1) The Institutional KPI Board, which displays students count, staff count, overall system pass rates, average attendance, and payment outstanding figures; (2) The Academic Structure Builder, which allows administrators to register new classes and subjects; (3) The Governance Roster, which lists student grades with administrative overrides; and (4) The Bank Proofs Verification Queue, where administrators approve parent payment proof uploads.",
            "The Admin Command Center also features a dynamic switcher to toggle between academic terms, adjusting dashboard metrics globally."
        ]
    },
    {
        title: "Section 18: The Elite Ranking Engine Details",
        h2: "Standard Competition Rankings and SQL Computations",
        paragraphs: [
            "Dubez Academy uses standard competition ranking (1-2-2-4). Dense, identical averages share a rank, and the next rank skips appropriate spots. To handle this efficiently, the system uses MySQL 8 Window functions.",
            "Previously, applications computed ranks using loops inside PHP, leading to performance bottlenecks when handling large student datasets. The Elite Ranking Engine shifts this work to the database, executing calculations in a single SQL query using `RANK() OVER (ORDER BY AVG(s.numeric_score) DESC)`.",
            "This query groups submissions by student ID, calculates the average score for the active term and year, and assigns a rank. This enables rapid recalculations even during major exam periods.",
            "Below is the SQL query used in the ranking engine:"
        ],
        code: `SELECT 
    s.student_id,
    AVG(s.numeric_score) AS overall_average,
    RANK() OVER (ORDER BY AVG(s.numeric_score) DESC) AS position
FROM wp_dubez_submissions s
INNER JOIN wp_usermeta um ON s.student_id = um.user_id
WHERE um.meta_key = 'student_class_id' AND um.meta_value = %d
AND s.academic_year = %s AND s.submission_term = %s
GROUP BY s.student_id`
    },
    {
        title: "Section 19: Growth Engine & Delta Calculations",
        h2: "Term-over-Term Comparison Engine",
        paragraphs: [
            "The Growth Engine measures term-over-term progress, calculating the percentage delta in child performance averages compared to the previous academic term.",
            "The engine identifies the active term (e.g. 2nd Term) and queries the database for the student's average in the preceding term (e.g. 1st Term). It calculates the mathematical delta: Current Term Average - Previous Term Average.",
            "This calculation displays visual indicators on student and parent dashboards: positive change triggers an upward arrow in green (↑ +[Delta]%), negative change logs a downward arrow in red (↓ [Delta]%), and no change renders a horizontal amber arrow (→ 0%). If no data is available for the previous term, the engine shows 'No Previous Data', preventing zero-division errors.",
            "This tracking system highlights performance trends over time."
        ]
    },
    {
        title: "Section 20: At-Risk Students Diagnostics Engine",
        h2: "Operational Interference Thresholds",
        paragraphs: [
            "The At-Risk Diagnostics Engine monitors student performance and flags individuals who fall below defined academic or engagement thresholds.",
            "The system evaluates four risk triggers: (1) An overall average score below 50%; (2) Placement in the bottom 20% of class rank (for classes containing over 15 students); (3) Zero graded submissions during the active term, suggesting a failure to submit work; (4) An attendance rate under 60%.",
            "Students flagged by the diagnostics engine are displayed on the Teacher Intervention Panel and the Administrator's Dashboard with detailed notes on the triggered alert (e.g., 'Attendance Danger: 45%', 'Failing Average: 42%').",
            "This automated detection system enables school staff to address student performance issues before the end of the term."
        ]
    },
    {
        title: "Section 21: Auto-Billing Run and Invoice Generation",
        h2: "Class-Based Term Billing Invoicing",
        paragraphs: [
            "Tuition fee management is automated to ensure financial security and accurate invoicing.",
            "The billing process uses the `wp_dubez_fee_structure` configurations defined by administrators for each class cohort and term. When a term begins, the administrator initiates the Auto-Billing Run. The billing engine retrieves a list of all students enrolled in the target class ID and inserts matching invoice records into the `wp_dubez_student_billing` table.",
            "Each student's invoice records their base fee amount, amount paid (initialized to 0.00), outstanding balance, and status set to 'unpaid'. If a student has a scholarship template applied, the adjusted amount column overrides the default base fee value.",
            "The billing ledger tracks tuition status throughout the academic term."
        ]
    },
    {
        title: "Section 22: Proof of Payment & Bank Verification Flow",
        h2: "Manual Bank Transfer Lifecycle",
        paragraphs: [
            "To support payments via manual bank transfers, the system features a ledger-matching workflow for parents and administrators.",
            "1. Upload Phase: The parent inputs the payment amount, attaches an image receipt, and submits the form. The system updates the billing record, sets the proof status to 'pending', and alerts administrators. The payment status remains 'unpaid' first.",
            "2. Vetting Phase: The administrator reviews the attached receipt against the bank statement. They can reject the receipt (resetting the status to 'rejected' with notes) or approve it. Approval updates the paid amount, recalculates the payment status ('paid' or 'partial'), and records the transaction in the ledger.",
            "This verified logging flow prevents double-entry errors and payment discrepancies."
        ]
    },
    {
        title: "Section 23: Relational Audit Ledger & Log Viewer",
        h2: "Historical Action Tracker & Governance audits",
        paragraphs: [
            "Every database change is audited to maintain the integrity of academic and financial records. Operations are logged to the `wp_dubez_audit_log` table.",
            "Any updates to grades, attendance, or billing configurations generate an audit trail. The log entry records the executor's ID, the affected student's ID, the action type, details of changed parameters, the active term, and a database timestamp.",
            "Administrators review these entries through a paginated log viewer. The page handles large datasets efficiently and enables sorting by action categories.",
            "Below is the core logging function in PHP:"
        ],
        code: `function dubez_log_action($action_type, $description) {
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'dubez_audit_log',
        [
            'user_id'     => get_current_user_id(),
            'action_type' => $action_type,
            'description' => $description,
            'term'        => get_option('dubez_current_term'),
            'academic_year'=> get_option('dubez_current_academic_year'),
            'created_at'  => current_time('mysql')
        ]
    );
}`
    },
    {
        title: "Section 24: Messaging System & Automated Alerts",
        h2: "Triggers for Direct Messaging and Broadcast Channels",
        paragraphs: [
            "The internal messaging module manages communications within the ERP. It supports direct notifications, system alerts, and global announcements.",
            "An hourly cron job runs background queries to check for alerts. If it detects unpaid balances, diagnostic risk flags, or attendance issues, it automatically logs target alerts. An internal query counts unread alerts, updating notification badges in the navigation menus in real-time.",
            "Announcements can also be broadcast globally by administrators to all roles, ensuring timely delivery of school notices.",
            "Below is the database query used to fetch user alerts:"
        ],
        code: `SELECT * FROM wp_dubez_messages
WHERE recipient_id = %d OR role_target IN (
    SELECT meta_value FROM wp_usermeta
    WHERE user_id = %d AND meta_key = 'wp_capabilities'
)
ORDER BY created_at DESC`
    },
    {
        title: "Section 25: ERP Security, escaping and nonces",
        h2: "Input Sanitation, Escaping, and Authentication Hardening",
        paragraphs: [
            "To secure the platform against SQL injection and cross-site scripting (XSS), the ERP enforces strict input sanitation, escaping, and authentication checks on all forms.",
            "Every frontend form utilizes WordPress nonces to prevent CSRF attacks. Scripts verify nonces before executing write queries, and inputs are processed using target functions: user IDs are validated with `intval()`, amounts are cast to floats with `floatval()`, and text fields are processed using `sanitize_text_field()` or `wp_kses_post()`.",
            "All SQL queries are run through helper functions like `$wpdb->prepare()`. This escapes queries beforehand, neutralizing SQL injection vectors. Frontend outputs use escaping functions like `esc_html()` and `esc_attr()` to protect against XSS exploits.",
            "This validation pattern reduces security vulnerabilities across the system."
        ]
    },
    {
        title: "Section 26: UI Style Guide & Responsive Typography",
        h2: "The Premium Aesthetics Variable Standard Map",
        paragraphs: [
            "The system UI features a sleek design, moving away from standard WordPress layouts for a premium institutional ERP appearance.",
            "Colors are defined as CSS variables using the HSL color model. The palette uses deep navy blue for structure elements, dark slate gray for side menus, and teal accents for highlights. A sans-serif font family, Outfit, is imported from Google Fonts to replace standard system fonts.",
            "Typography rules use a typographic scale: H1 is sized at 32px/1.4 line-height, H2 at 24px, and body copy at 14px to maximize readability.",
            "The interface adapts to tablet and mobile screens dynamically using CSS breakpoints."
        ]
    },
    {
        title: "Section 27: Theme Design Assets & Responsive CSS",
        h2: "CSS Variable Maps & Layout Breakpoints",
        paragraphs: [
            "To support dark and light theme options, colors are mapped to CSS custom variables in the style sheets.",
            "A global script intercepts user preferences, toggle actions saving settings to local storage and appending appropriate class names to the HTML body. Spacing uses standard margins (8px, 16px, 24px, 48px) to align page panels, while layouts use Flexbox wrapping to adapt grids to different screen sizes.",
            "Animations, such as hover changes on menu options, use transient styling to improve usability.",
            "CSS stylesheets are separated from coding templates, keeping formatting logic decoupled."
        ]
    },
    {
        title: "Section 28: System Deployment & Maintenance Checklist",
        h2: "PHP Requirements and Deployment Steps",
        paragraphs: [
            "To deploy the Dubez Academy system in a production environment, the hosting environment must meet specific prerequisites.",
            "Prerequisites: (1) Apache/Nginx running PHP 8.0 or newer; (2) MySQL 8.0 or newer, required for ranking window functions; (3) WordPress 6.2 core system installed. Deployment steps include upload of the plugin directory, migration of database tables, creation of WordPress page routes, and configuring roles.",
            "Folders, specifically file upload directories, need write permissions but must block direct PHP execution.",
            "We recommend scheduling weekly backups to safeguard academic records."
        ]
    },
    {
        title: "Section 29: Software Engineering Analysis",
        h2: "Database Normalization and Cohesion Metrics",
        paragraphs: [
            "The ERP design is structured to prioritize database normalization, relational integrity, and high class cohesion.",
            "The design conforms to Third Normal Form (3NF). Every field relies solely on the primary key, eliminating multi-column updates and data anomalies. Relational tables replace generic metadata tables, reducing database storage size.",
            "Functional modules are decoupled. Grading calculations, billing, and page rendering operate independently. This separation ensures that updates to the user interface do not affect core calculations or databases.",
            "This structural approach provides a stable foundation for the ERP platform."
        ]
    },
    {
        title: "Section 30: Future Roadmap: Charts & Graph (Phase 5)",
        h2: "Visual Dashboard Integration Plans",
        paragraphs: [
            "The Phase 5 roadmap introduces visual data widgets to the Parent and Teacher Portal interfaces during the upcoming trimester upgrade.",
            "The upgrade integrates Chart.js into portal views. Key features include: (1) Performance charts showing student averages over time; (2) Attendance indicators showing monthly presence-to-absence ratios; (3) Subject grade maps comparing student performance against class averages; (4) Color-coded markers highlighting academic at-risk levels.",
            "These charts compile data calls from the database in real-time, loading widgets using AJAX to minimize page load times."
        ]
    },
    {
        title: "Section 31: Future Roadmap: Scaling & Hardening (Phase 8)",
        h2: "Performance Tuning and security auditing",
        paragraphs: [
            "The Phase 8 roadmap focuses on performance tuning, optimizing database transactions, and implementing additional security measures.",
            "For performance optimization, target changes include: (1) Query pagination for large rosters; (2) Caching algorithms to store calculated rankings; (3) Relational cleanup scripts to purge expired logs; (4) Hardened nonce verification on forms.",
            "These updates aim to optimize database response times and ensure system stability as class cohorts expand."
        ]
    },
    {
        title: "Section 32: Future Roadmap: Multi-Institutional SaaS (Phase 9)",
        h2: "Multitenant SaaS Relational Schema Upgrade",
        paragraphs: [
            "The Phase 9 update expands the ERP to a SaaS configuration, allowing a single installation to support multiple schools.",
            "The design uses a partitioned database schema: (1) An Institutions table registers schools; (2) An tenant ID column is added to all tables, filtering records to isolate school data; (3) Invoicing systems manage payment processes for each school independently; (4) Portal layouts can be customized with school branding.",
            "This architecture allows for multitenant deployments while maintaining database security and isolation."
        ]
    },
    {
        title: "Section 33: Conclusion, Developer Sign-off & Appendix",
        h2: "Release Verification and Capabilities Mapping",
        paragraphs: [
            "The Dubez Academy Custom ERP is operational, providing a relational framework for managing school operations.",
            "By implementing relational database structures, standard rankings, automated billing runs, and transaction audit trails, the platform delivers school management features while maintaining database integrity.",
            "Below is a reference guide mapping system capabilities to roles:"
        ],
        table: {
            headers: ["System Capability", "School Admin", "Class Teacher", "Student Portal", "Parent Portal"],
            rows: [
                ["dubez_manage_context", "YES (Authoritative)", "NO ACCESS", "NO ACCESS", "NO ACCESS"],
                ["dubez_manage_fees", "YES (Authoritative)", "NO ACCESS", "NO ACCESS", "NO ACCESS"],
                ["dubez_generate_billing", "YES (Authoritative)", "NO ACCESS", "NO ACCESS", "NO ACCESS"],
                ["dubez_approve_payment", "YES (Authoritative)", "NO ACCESS", "NO ACCESS", "NO ACCESS"],
                ["dubez_mark_attendance", "YES (Override rights)", "YES (Class Cohort)", "NO ACCESS", "NO ACCESS"],
                ["dubez_grade_assignment", "YES (Override rights)", "YES (Subject List)", "NO ACCESS", "NO ACCESS"],
                ["dubez_send_announcement", "YES (School Broad)", "YES (Class Broad)", "NO ACCESS", "NO ACCESS"],
                ["dubez_view_academic_reports", "YES (Full access)", "YES (Class Cohort)", "YES (Personal)", "YES (Children)"]
            ]
        }
    }
];

// Append each chapter with an H1, H2, paragraphs, code blocks, tables, and PageBreak
chapters.forEach((ch, idx) => {
    docChildren.push(makeH1(ch.title));
    docChildren.push(makeH2(ch.h2));
    
    ch.paragraphs.forEach(p => {
        docChildren.push(makeText(p));
    });

    if (ch.table) {
        docChildren.push(makeTable(ch.table.headers, ch.table.rows));
        docChildren.push(new Paragraph({ spacing: { before: 100 } }));
    }

    if (ch.extraParagraphs) {
        ch.extraParagraphs.forEach(p => {
            docChildren.push(makeText(p));
        });
    }

    if (ch.table2) {
        docChildren.push(makeTable(ch.table2.headers, ch.table2.rows));
        docChildren.push(new Paragraph({ spacing: { before: 100 } }));
    }

    if (ch.code) {
        docChildren.push(...makeCodeBlock(ch.code));
        docChildren.push(new Paragraph({ spacing: { before: 100 } }));
    }

    // Add pagebreak to all chapters except the very last one
    if (idx < chapters.length - 1) {
        docChildren.push(new Paragraph({children:[new PageBreak()]}));
    }
});

// Create Document object
const doc = new Document({
    sections: [
        {
            properties: {
                page: {
                    pageNumbers: {
                        start: 1,
                        formatType: "DECIMAL",
                    },
                },
            },
            headers: {
                default: new Header({
                    children: [
                        new Paragraph({
                            alignment: AlignmentType.RIGHT,
                            spacing: { after: 120 },
                            children: [
                                new TextRun({
                                    text: "DUBEZ ACADEMY ERP — CORE SPECIFICATIONS DOCUMENT (MASTER ARCHITECTURE)",
                                    font: "Segoe UI",
                                    size: 14,
                                    color: "718096",
                                    italic: true
                                })
                            ]
                        })
                    ]
                })
            },
            footers: {
                default: new Footer({
                    children: [
                        new Paragraph({
                            alignment: AlignmentType.CENTER,
                            children: [
                                new TextRun({ text: "Page ", font: "Segoe UI", size: 16, color: "718096" }),
                                PageNumber.CURRENT,
                                new TextRun({ text: " of ", font: "Segoe UI", size: 16, color: "718096" }),
                                PageNumber.TOTAL_PAGES
                            ]
                        })
                    ]
                })
            },
            children: docChildren
        }
    ]
});

// Save to disk
Packer.toBuffer(doc).then((buffer) => {
    fs.writeFileSync("../Dubez_Academy_ERP_Project_Report.docx", buffer);
    console.log("Successfully generated Dubez_Academy_ERP_Project_Report.docx");
}).catch((err) => {
    console.error("Error generating docx:", err);
});
