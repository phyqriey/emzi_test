import { createApp } from 'vue';
import 'bootstrap/dist/css/bootstrap.css';
import EmployeeList from './components/EmployeeList.vue';
import Toast, { POSITION } from 'vue-toastification';
import 'vue-toastification/dist/index.css';

const app = createApp({
  components: {
    EmployeeList
  }
});
app.use(Toast, {
    position: POSITION.TOP_RIGHT,
    timeout: 3000,
});
app.mount('#app');
