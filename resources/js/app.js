import './bootstrap'
// import $ from "jquery";

/* sweetalert2 */
import Swal from 'sweetalert2';
window.Swal = Swal;

/* datatables */
// import "datatables.net-dt";
// import './dataTables/table';

/* selects */
// import 'select2';
// import select2 from 'select2';
// import 'select2/dist/css/select2.css';
// import 'select2/dist/css/select2.min.css';
// select2();

/* toastr */
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';


/* common */
import { togglePasswordVisibility } from './common/password-utils';

window.toastr = toastr;
window.togglePasswordVisibility = togglePasswordVisibility;
