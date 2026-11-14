import './bootstrap';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import Swal from 'sweetalert2';
import './login';
import './dashboard-sync';


window.Swal = Swal.mixin({ confirmButtonColor: '#166534', cancelButtonColor: '#64748b', denyButtonColor: '#b91c1c' });
window.Alpine = Alpine;

window.Livewire = Livewire;
Livewire.start();

