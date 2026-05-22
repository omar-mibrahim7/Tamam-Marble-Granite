// ============================================================
// NAVBAR.JS
// ============================================================

// MENU
function toggleMenu(){
  const menu = document.getElementById("menu");
  if(!menu) return;
  menu.style.left = menu.style.left === "0px" ? "-260px" : "0px";
}

// SEARCH TOGGLE
function toggleSearch(){
  const input = document.getElementById("searchInput");
  if(!input) return;
  input.classList.toggle("active");
  input.focus();
}

// SEARCH
const searchInput = document.getElementById("searchInput");
const resultBox = document.createElement("div");
resultBox.id = "searchResults";
const searchContainer = document.querySelector(".search-container");
if(searchContainer){
    searchContainer.appendChild(resultBox);
} else {
    document.body.appendChild(resultBox);
}
if (searchInput) {
  let searchTimer;

  searchInput.addEventListener("input", function () {
    const value = this.value.trim();
    clearTimeout(searchTimer);
    resultBox.innerHTML = "";

    if (value === "") {
      resultBox.style.display = "none";
      return;
    }

    searchTimer = setTimeout(() => {
fetch(`/tamam/Controller/search_products.php?q=${encodeURIComponent(value)}`)
        .then(res => res.json())
        .then(data => {
          resultBox.innerHTML = "";

          if (data.length === 0) {
            const item = document.createElement("div");
            item.innerText = "No results found";
            item.style.color = "gray";
            item.style.cursor = "default";
            item.style.textAlign = "center";
            item.style.padding = "12px";
            resultBox.appendChild(item);
          } else {
            data.forEach(product => {
              const item = document.createElement("div");
              item.classList.add("search-result-item");
              item.innerHTML = `
                <img src="${product.image_url}" alt="${product.product_name}">
                <div>
                  <span class="result-name">${product.product_name}</span>
                  <span class="result-type">${product.product_type}</span>
                </div>
              `;
             item.onclick = () => {
              const page = product.product_type === 'Marble' ? 'mardetails.php' : 'gradetails.php';
               window.location.href = `/tamam/View/php/${page}?id=${product.product_id}`;
              };
              resultBox.appendChild(item);
            });
          }

          resultBox.style.display = "block";
        })
        .catch(() => {
          resultBox.style.display = "none";
        });
    }, 300);
  });
}

document.addEventListener("click", function (e) {
  const search = document.getElementById("searchInput");
  if (search && !search.contains(e.target) && !resultBox.contains(e.target)) {
    resultBox.style.display = "none";
  }
});

// USER ICON
const userLink = document.getElementById("userLink");
const userIcon = document.getElementById("userIcon");

if(userLink && userIcon){
  const isLoggedIn = userLink.dataset.loggedIn === "true";
  userLink.title = isLoggedIn ? "My Account" : "Login";

  if(isLoggedIn){
    userIcon.classList.remove("fa-right-to-bracket");
    userIcon.classList.add("fa-circle-user");
  } else {
    userIcon.classList.remove("fa-circle-user");
    userIcon.classList.add("fa-right-to-bracket");
  }
  
}
// CART COUNT
const cartLink = document.querySelector(".right-icons a[href='cart.php']");
if (cartLink) {
    fetch('/tamam/Controller/cart_count.php')
        .then(res => res.json())
        .then(data => {
            if (!data.loggedIn) {
                // لو مش logged in، غير الـ href
                cartLink.href = "#";
                cartLink.addEventListener("click", (e) => {
                    e.preventDefault();
                    // popup بسيط
                    const existing = document.getElementById("cartPopup");
                    if (existing) existing.remove();

                    const popup = document.createElement("div");
                    popup.id = "cartPopup";
                    popup.innerHTML = `
                        <div id="cartPopupBox">
                            <p>Please login to view your cart</p>
                            <a href="/tamam/View/php/login.php">Login</a>
                            <a href="/tamam/View/php/register.php">Register</a>
                            <span id="closePopup">✕</span>
                        </div>
                    `;
                    document.body.appendChild(popup);

                    document.getElementById("closePopup").onclick = () => popup.remove();
                });
            } else if (data.count > 0) {
                // بين العدد على الأيقونة
                const badge = document.createElement("span");
                badge.id = "cartBadge";
                badge.innerText = data.count;
                cartLink.style.position = "relative";
                cartLink.appendChild(badge);
            }
        });
}