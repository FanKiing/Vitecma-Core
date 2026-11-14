import './bootstrap';
import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import './login';
import './dashboard-sync';


window.Swal = Swal.mixin({ confirmButtonColor: '#166534', cancelButtonColor: '#64748b', denyButtonColor: '#b91c1c' });
window.Alpine = Alpine;

Alpine.start();

