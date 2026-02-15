class DateRangeHelper {
    static formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    static getDateRange(rangeType) {
        const now = new Date();
        let start, end;

        switch (rangeType) {
            case 'daily':
                start = new Date(now);
                end = new Date(now);
                break;
            case 'weekly':
                start = new Date(now);
                start.setDate(now.getDate() - now.getDay());
                end = new Date(start);
                end.setDate(start.getDate() + 6);
                break;
            case 'monthly':
                start = new Date(now.getFullYear(), now.getMonth(), 1);
                end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                break;
            case 'yearly':
                start = new Date(now.getFullYear(), 0, 1);
                end = new Date(now.getFullYear(), 11, 31);
                break;
            default:
                start = null;
                end = null;
        }

        return {
            start: start ? this.formatDate(start) : '',
            end: end ? this.formatDate(end) : ''
        };
    }
}

class MoneyFormatter {
    static format(value) {
        if (isNaN(value)) return "₱0.00";
        return "₱" + Number(value).toLocaleString("en-PH", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
}

class SalesReportService {
  constructor(apiBasePath) { this.apiBasePath = apiBasePath; }

  async loadReport(start, end) {
    let url = `${this.apiBasePath}/salesreport`;
    if (start && end) url += `?start=${start}&end=${end}`;
    try { const res = await fetch(url); return await res.json(); }
    catch (err) { console.error(err); return { status:false,message:err.message }; }
  }

  async loadOrderDetails(orderId) {
    try {
      const res = await fetch(`${this.apiBasePath}/salesreport?id=${orderId}`);
      return await res.json();
    } catch (err) {
      console.error(err);
      return { status:false, message:err.message };
    }
  }
}


class SalesReportController {
    constructor(apiBasePath) {
        this.service = new SalesReportService(apiBasePath);
        this.startDateEl = document.getElementById('startDate');
        this.endDateEl = document.getElementById('endDate');
        this.totalSalesEl = document.getElementById('totalSales');
        this.totalOrdersEl = document.getElementById('totalOrders');
        this.topSellingTableEl = document.getElementById('topSellingTable');

        // ✅ New orders table element
        this.ordersTableEl = document.getElementById('ordersTable');


        this.rangeButtons = document.querySelectorAll('button[data-range]');
    }

    init() {
        this.bindEvents();
        this.resetDateInputs();
        this.fetchAndRender();
    }

  bindEvents() {

    this.rangeButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const range = btn.getAttribute('data-range');
        const { start, end } = DateRangeHelper.getDateRange(range);
        this.startDateEl.value = start;
        this.endDateEl.value = end;
        this.fetchAndRender(start, end);
      });
    });

    // ✅ Added here
    const exportBtn = document.getElementById('exportExcel');
    if (exportBtn) {
      exportBtn.addEventListener('click', () => {
        if (!this.latestReportData) {
          alert("Please load a report first!");
          return;
        }
        this.exportToExcel(this.latestReportData);
      });
    }
  }


    resetDateInputs() {
        this.startDateEl.value = '';
        this.endDateEl.value = '';
    }

  async fetchAndRender(start = '', end = '') {
    const data = await this.service.loadReport(start, end);
    if (!data.status) {
      alert(data.message || "Failed to load sales report");
      return;
    }

    // Store for export
    this.latestReportData = data;

    this.totalSalesEl.textContent = MoneyFormatter.format(data.total_sales);
    this.totalOrdersEl.textContent = data.total_orders;
    this.renderTopSelling(data.top_selling);
    this.renderOrders(data.orders || []);
  }


    renderTopSelling(items) {
        this.topSellingTableEl.innerHTML = "";
        items.forEach(item => {
            const revenue = item.total_revenue ? MoneyFormatter.format(item.total_revenue) : "₱0.00";
            const row = `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.total_sold}</td>
                    <td>${revenue}</td>
                </tr>`;
            this.topSellingTableEl.innerHTML += row;
        });
    }

renderOrders(orders) {
    if (!this.ordersTableEl) return;
    this.ordersTableEl.innerHTML = "";

    orders.forEach(order => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${order.id}</td>
            <td>${order.customer}</td>
            <td>${order.placed_at}</td>
            <td>${MoneyFormatter.format(order.final_amount)}</td>
            <td><button class="view-details" data-id="${order.id}">View Details</button></td>
        `;
        this.ordersTableEl.appendChild(row);
    });

    this.ordersTableEl.querySelectorAll('.view-details').forEach(btn => {
        btn.addEventListener('click', e => {
            const orderId = e.target.getAttribute('data-id');
            this.showOrderDetails(orderId);
        });
    });
}

showOrderDetails(orderId) {
  this.service.loadOrderDetails(orderId).then(data => {
    if (!data.status) {
      alert(data.message || "Failed to load order details");
      return;
    }

    const items = data.items || [];
    const receipt = data.receipt || {};

    let html = `
      <div class="modal-overlay">
        <div class="modal">
          <h2>Order #${orderId} Details</h2>
          <p><strong>Customer:</strong> ${receipt.customer || "-"}</p>
          <p><strong>Date:</strong> ${receipt.issued_at || receipt.placed_at || "-"}</p>
          <p><strong>Receipt Code:</strong> ${receipt.receipt_code || "-"}</p>

          <table>
            <thead>
              <tr>
                <th>Item</th>
                <th>Size</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              ${items.map(it => `
                <tr>
                  <td>${it.item_name}</td>
                  <td>${it.size_name || '-'}</td>
                  <td>${it.item_type}</td>
                  <td>${it.quantity}</td>
                  <td>${MoneyFormatter.format(it.unit_price)}</td>
                  <td>${MoneyFormatter.format(it.total_price)}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>

          <div class="summary">
            <p><strong>Discount:</strong> ${receipt.discount_type || 'none'} 
                (${receipt.discount_value || 0}% → ${MoneyFormatter.format(receipt.discount_amount || 0)})</p>
            <p><strong>Final Amount:</strong> ${MoneyFormatter.format(receipt.final_amount || 0)}</p>
            <p><strong>Paid:</strong> ${MoneyFormatter.format(receipt.payment_amount || 0)}</p>
            <p><strong>Change:</strong> ${MoneyFormatter.format(receipt.change_amount || 0)}</p>
          </div>

          <button id="closeModal">Close</button>
        </div>
      </div>`;

    document.body.insertAdjacentHTML("beforeend", html);
    document.getElementById("closeModal").addEventListener("click", () => {
      document.querySelector(".modal-overlay").remove();
    });
  });
}

async exportToExcel(data) {
  const wb = XLSX.utils.book_new();
  const detailedOrders = [];

  // ✅ Fetch full details for each order using the same API as "View Details"
  for (const order of data.orders || []) {
    try {
      const details = await this.service.loadOrderDetails(order.id);
      if (!details.status) continue;

      const receipt = details.receipt || {};
      const items = details.items || [];

      if (items.length === 0) {
        detailedOrders.push({
          "Order ID": order.id,
          "Customer": receipt.customer || order.customer || "-",
          "Date": receipt.issued_at || order.placed_at || "-",
          "Receipt Code": receipt.receipt_code || "-",
          "Item Name": "-",
          "Size": "-",
          "Type": "-",
          "Quantity": "-",
          "Unit Price": "-",
          "Item Total": "-",
          "Discount Type": receipt.discount_type || "none",
          "Discount Value (%)": receipt.discount_value || 0,
          "Discount Amount": receipt.discount_amount || 0,
          "Final Amount": receipt.final_amount || 0,
          "Paid": receipt.payment_amount || 0,
          "Change": receipt.change_amount || 0,
        });
      } else {
        items.forEach(it => {
          detailedOrders.push({
            "Order ID": order.id,
            "Customer": receipt.customer || order.customer || "-",
            "Date": receipt.issued_at || order.placed_at || "-",
            "Receipt Code": receipt.receipt_code || "-",
            "Item Name": it.item_name,
            "Size": it.size_name || "-",
            "Type": it.item_type || "-",
            "Quantity": it.quantity,
            "Unit Price": it.unit_price,
            "Item Total": it.total_price,
            "Discount Type": receipt.discount_type || "none",
            "Discount Value (%)": receipt.discount_value || 0,
            "Discount Amount": receipt.discount_amount || 0,
            "Final Amount": receipt.final_amount || 0,
            "Paid": receipt.payment_amount || 0,
            "Change": receipt.change_amount || 0,
          });
        });
      }

    } catch (err) {
      console.error(`Failed to load details for order #${order.id}:`, err);
    }
  }

  // ✅ Create Excel sheet
  const ordersSheet = XLSX.utils.json_to_sheet(detailedOrders);
  XLSX.utils.book_append_sheet(wb, ordersSheet, "SALES REPORT");

  // ✅ Save Excel file
  XLSX.writeFile(wb, "SALES_REPORT.xlsx");
}

  

}




// ===== Initialization =====
window.addEventListener('DOMContentLoaded', () => {
    const controller = new SalesReportController(API_BASE_PATH);
    controller.init();
});
