<template>
  <div class="container mt-5">
    <h1 class="text-center mb-4">Employee List</h1>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <!-- Search Input -->
      <input v-model="searchQuery" @input="searchEmployees" placeholder="Search employees..."
        class="form-control w-50" />

      <!-- Results Per Page Selector -->
      <div>
        <label for="resultsPerPage" class="form-label me-2">Show</label>
        <select id="resultsPerPage" v-model="resultsPerPage" @change="updateResultsPerPage" class="form-select"
          style="width: auto; display: inline-block;">
          <option v-for="option in resultsOptions" :key="option" :value="option">{{ option }}</option>
        </select>
        <span>results per page</span>
      </div>
    </div>

    <!-- Loading Indicator -->
    <div v-if="isLoading" class="text-center my-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Employee Table -->
    <table v-if="!isLoading && employees.length > 0" class="table table-striped table-hover">
      <thead class="table-dark">
        <tr>
          <th scope="col">Name</th>
          <th scope="col">Position</th>
          <th scope="col">Department</th>
          <th scope="col">Phone</th>
          <th scope="col">Email</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="employee in employees" :key="employee.id">
          <td>{{ employee.first_name }} {{ employee.last_name }}</td>
          <td>{{ employee.position }}</td>
          <td>{{ employee.department }}</td>
          <td>{{ employee.phone_number }}</td>
          <td>{{ employee.email }}</td>
        </tr>
      </tbody>
    </table>

    <!-- No Results Message -->
    <div v-if="!isLoading && employees.length === 0" class="text-center my-4">
      <p>No employees found. Try adjusting your search.</p>
    </div>

    <!-- Results Count and Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4" v-if="!isLoading">
      <div>
        <small>
          Showing
          {{ (pagination.current_page - 1) * resultsPerPage + 1 }}
          to
          {{ Math.min(pagination.current_page * resultsPerPage, pagination.total) }}
          of {{ pagination.total }} results
        </small>
      </div>
      <!-- Pagination Links -->
      <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination mb-0">
          <li v-if="pagination.current_page !== 1" class="page-item">
            <button class="page-link" @click="goToPage(pagination.prev_page_url)">
              << </button>
          </li>

          <!-- Render Limited Links with Ellipses and First/Last Page -->
          <li v-for="link in limitedLinks" :key="link.label"
            :class="{ active: link.active, disabled: !link.url, 'page-item': link.url || link.label === '...' }">
            <button class="page-link" v-if="link.url" @click="goToPage(link.url)" v-html="link.label"></button>
            <span v-else class="page-link" v-html="link.label"></span>
          </li>

          <li v-if="pagination.current_page !== pagination.last_page" class="page-item">
            <button class="page-link" @click="goToPage(pagination.next_page_url)">
              >>
            </button>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Pusher from 'pusher-js';
import { useToast } from 'vue-toastification';

export default {
  data() {
    return {
      employees: [],
      pagination: {
        current_page: 1,
        total: 0,
        links: [],
        last_page: 1,
      },
      searchQuery: '',
      isLoading: false,
      resultsPerPage: 10,  // Default results per page
      resultsOptions: [10, 20, 30, 40, 50],  // Options for results per page
      sideLinks: 1,  // Number of links on each side of the current page
      currentURL: '',
    };
  },
  computed: {
    limitedLinks() {
      const { current_page, last_page, links } = this.pagination;
      const start = Math.max(1, current_page - this.sideLinks);
      const end = Math.min(last_page, current_page + this.sideLinks);
      const displayedLinks = [];

      // "First" page link
      if (start > 1) {
        displayedLinks.push({
          url: links[1]?.url || null, // Safe access to handle undefined
          label: '1',
          active: current_page === 1,
        });
        if (start > 2) {
          displayedLinks.push({
            url: null,
            label: '...',
            active: false,
          });
        }
      }

      // Page links around the current page
      for (let page = start; page <= end; page++) {
        links.forEach(link => {
          if (parseInt(link.label) === page) {
            displayedLinks.push({
              url: link.url || null,
              label: String(page),
              active: page === current_page,
            });
          }
        });
      }

      // "Last" page link
      if (end < last_page) {
        if (end < last_page - 1) {
          displayedLinks.push({
            url: null,
            label: '...',
            active: false,
          });
        }
        displayedLinks.push({
          url: links[links.length - 2]?.url || null, // Safe access to handle undefined
          label: String(last_page),
          active: current_page === last_page,
        });
      }

      return displayedLinks.length > 1 ? displayedLinks : [];
    },
  },

  methods: {
    async fetchEmployees(pageUrl = `/api/employees?per_page=${this.resultsPerPage}`) {
      this.isLoading = true;
      this.currentURL = pageUrl;
      try {
        const response = await axios.get(pageUrl);
        this.employees = response.data.data;
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          total: response.data.total,
          links: response.data.links,
          prev_page_url: response.data.prev_page_url,
          next_page_url: response.data.next_page_url,
        };
      } catch (error) {
        this.employees = [];
        console.error('Error fetching employee data:', error);
        alert('Failed to load employee data. Please try again later.');
      } finally {
        this.isLoading = false;
      }
    },
    async searchEmployees() {
      this.isLoading = true;
      try {
        this.currentURL = `/api/employees/search?query=${this.searchQuery}&per_page=${this.resultsPerPage}`;
        const response = await axios.get(this.currentURL);
        this.employees = response.data.data;
        this.pagination = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          total: response.data.total,
          links: response.data.links,
          prev_page_url: response.data.prev_page_url,
          next_page_url: response.data.next_page_url,
        };
      } catch (error) {
        this.employees = [];
        console.error('Error searching employees:', error);
        alert('Search failed. Please try again.');
      } finally {
        this.isLoading = false;
      }
    },
    updateResultsPerPage() {
      if (this.searchQuery.trim != '') {
        this.searchEmployees();
      } else {
        this.fetchEmployees();
      }
    },
    goToPage(url) {
      if (url) {
        this.fetchEmployees(url);
      }
    },
  },
  mounted() {
    this.fetchEmployees();
    const toast = useToast();

    // Pusher integration for real-time updates
    const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    });

    const channel = pusher.subscribe('employees');
    channel.bind('App\\Events\\EmployeeCreated', (data) => {
      console.log(data);
      toast.success(`New Employee Added: ${data.employee.first_name} ${data.employee.last_name}`, {
        timeout: 5000,
      });
      this.fetchEmployees(this.currentURL);
      // this.employees.unshift(data.employee); // Add new employee to top of list
      // if (this.employees.length > this.resultsPerPage) {
      //     this.employees.pop(); // Remove last employee if list exceeds `resultsPerPage`
      // }
      // this.pagination.total += 1; // Update total count
    });
  },
};
</script>

<style scoped>
/* Additional styling for better layout */
.container {
  max-width: 800px;
}

.pagination .page-item.active .page-link {
  background-color: #007bff;
  color: #fff;
  border-color: #007bff;
}
</style>
