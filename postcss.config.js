import rtlcss from 'rtlcss';

// The site is Persian-first and RTL-only. Bootstrap is compiled from SCSS in
// its native LTR form and the *whole* stylesheet is then flipped by RTLCSS —
// the same tool Bootstrap uses to produce its official bootstrap.rtl.css.
// Bootstrap's SCSS already contains the /* rtl:... */ control directives
// RTLCSS needs, so no manual overrides are required. See docs/FRONTEND.md.
export default {
    plugins: [rtlcss()],
};
