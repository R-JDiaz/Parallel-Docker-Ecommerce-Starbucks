import { API_BASE_PATH } from '../js/config.js';
export function updateProfileImageInHeader(imageUrl) {
    const event = new CustomEvent('profileImageUpdated', {
        detail: { imageUrl: imageUrl }
    });
    document.dispatchEvent(event);
}

export async function loadUserProfile() {
    const res = await fetch(`${API_BASE_PATH}/profile`, {
      method: "GET",
      credentials: "include"
    });
    if (!res.ok) return;
    const data = await res.json();
    if (!data.status) return;

    const user = data.user;

    // Fill text fields
    document.getElementById("first_name").value = user.first_name || "";
    document.getElementById("middle_name").value = user.middle_name || "";
    document.getElementById("last_name").value = user.last_name || "";
    document.getElementById("street").value = user.address?.street || "";

    // 🔹 Load dropdowns
    await loadCountries();
    if (user.address?.country) {
      document.getElementById("country").value = user.address.country;
      await loadProvinces(user.address.country);

      if (user.address?.province) {
        document.getElementById("province").value = user.address.province;
        await loadCities(user.address.province);

        if (user.address?.city) {
          document.getElementById("city").value = user.address.city;
        }
      }
    }
}

export async function loadCountries() {
  const res = await fetch(`${API_BASE_PATH}/getCountries`);
  const countries = await res.json();
  const select = document.getElementById("country");
  if (!select) return;
  select.innerHTML = `<option value="">-- Select Country --</option>`;
  countries.forEach(c => {
    select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
  });
}

export async function loadProvinces(countryId) {
  const res = await fetch(`${API_BASE_PATH}/getProvince?country_id=${countryId}`);
  const provinces = await res.json();
  const select = document.getElementById("province");
  if (!select) return;
  select.innerHTML = `<option value="">-- Select Province --</option>`;
  provinces.forEach(p => {
    select.innerHTML += `<option value="${p.id}">${p.name}</option>`;
  });
}

export async function loadCities(provinceId) {
  const res = await fetch(`${API_BASE_PATH}/getCities?province_id=${provinceId}`);
  const cities = await res.json();
  const select = document.getElementById("city");
  if (!select) return;
  select.innerHTML = `<option value="">-- Select City --</option>`;
  cities.forEach(c => {
    select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
  });
}

// Ensure CSS variables are defined
export function ensureCSSVariables() {
    if (!document.documentElement.style.getPropertyValue('--main-color-darkgreen')) {
        const style = document.createElement('style');
        style.textContent = `
            :root {
                --main-color-green: #00704a;
                --main-color-darkgreen: #00482b;
                --main-color-lightgreen: #009959;
            }
        `;
        document.head.appendChild(style);
    }
}

