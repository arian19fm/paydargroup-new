// Bootstrap JS — selective imports.
//
// Each import registers its data-API (data-bs-toggle="...") automatically.
// Importing individual modules instead of the whole library keeps the
// bundle small; add a module here only when markup needs it.

import Collapse from 'bootstrap/js/dist/collapse';
import Dropdown from 'bootstrap/js/dist/dropdown';
import Offcanvas from 'bootstrap/js/dist/offcanvas';
import Modal from 'bootstrap/js/dist/modal';
import Alert from 'bootstrap/js/dist/alert';

// Exposed for the rare case of programmatic use from inline scripts.
window.bootstrap = { Collapse, Dropdown, Offcanvas, Modal, Alert };

// Not imported (add when needed): tooltip, popover (+Popper), carousel, tab,
// toast, scrollspy, button.
