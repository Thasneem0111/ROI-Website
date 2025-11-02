Visitor tracking: quick instructions

1. How it works

- `admin/track_visit.php` records a visit and creates the `visitors` table if missing.
- The snippet (in `admin/visit_snippet.html`) should be included on public pages. It posts the current path and sets a cookie `roi_visitor` to identify unique visitors.

2. Quick QA (simulate visits)

- Open in your browser:
  http://localhost/ROIwebsite/admin/simulate_visits.php
- The script inserts sample visitor rows (5 this week, 3 last week) and returns JSON with inserted keys and the resulting counts.
- After running the simulation, open the dashboard to confirm the boxes update: http://localhost/ROIwebsite/admin/dashboard.php

3. Deploy snippet to production pages

- Copy the contents of `admin/visit_snippet.html` into your public site template (recommended just before </body>), or add a server-side include.

4. Notes & privacy

- The tracker sets a 1-year cookie. Update your privacy policy if required.
- This is a simple analytics store for small sites. For enterprise-grade analytics use GA/Matomo.
