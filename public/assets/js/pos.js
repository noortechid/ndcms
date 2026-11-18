document.addEventListener('DOMContentLoaded', () => {
    const productCards = document.querySelectorAll('.product-card');
    const cartItems = document.getElementById('cart-items');
    const cartInputs = document.getElementById('cart-inputs');
    const emptyCart = document.getElementById('empty-cart');

    const searchInput = document.getElementById('product-search');
    const clearButton = document.getElementById('clear-cart');

    const discountInput = document.getElementById('discount');
    const paymentMethod = document.getElementById('payment_method');
    const amountPaidInput = document.getElementById('amount_paid');

    const subtotalElement = document.getElementById('subtotal');
    const totalElement = document.getElementById('total');
    const changeElement = document.getElementById('change');
    const checkoutButton = document.getElementById('checkout-button');
    const checkoutForm = document.getElementById('checkout-form');

    if (!cartItems || !checkoutForm) {
        return;
    }

    const cart = new Map();

    const formatMoney = (amount) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(amount);
    };

    const toNumber = (value) => {
        const number = Number(value);

        return Number.isFinite(number) && number >= 0
            ? number
            : 0;
    };

    const addProduct = (card) => {
        const id = Number(card.dataset.id);
        const name = card.dataset.name;
        const price = toNumber(card.dataset.price);

        if (!Number.isInteger(id) || id <= 0 || !name || price <= 0) {
            return;
        }

        if (cart.has(id)) {
            const item = cart.get(id);

            if (item.quantity < 999) {
                item.quantity++;
            }
        } else {
            cart.set(id, {
                id,
                name,
                price,
                quantity: 1,
            });
        }

        renderCart();
    };

    const updateQuantity = (id, change) => {
        const item = cart.get(id);

        if (!item) {
            return;
        }

        item.quantity += change;

        if (item.quantity <= 0) {
            cart.delete(id);
        } else if (item.quantity > 999) {
            item.quantity = 999;
        }

        renderCart();
    };

    const calculateTotals = () => {
        let subtotal = 0;

        cart.forEach((item) => {
            subtotal += item.price * item.quantity;
        });

        const discount = Math.min(
            toNumber(discountInput.value),
            subtotal
        );

        const total = Math.max(0, subtotal - discount);

        return {
            subtotal,
            discount,
            total,
        };
    };

    const renderCart = () => {
        cartItems.replaceChildren();
        cartInputs.replaceChildren();

        emptyCart.classList.toggle('hidden', cart.size > 0);

        let index = 0;

        cart.forEach((item) => {
            const wrapper = document.createElement('div');
            wrapper.className =
                'flex items-center justify-between gap-3 border-b border-[#F5EEEA] pb-3';

            const information = document.createElement('div');
            information.className = 'min-w-0 flex-1';

            const name = document.createElement('p');
            name.className = 'text-sm font-semibold truncate';
            name.textContent = item.name;

            const price = document.createElement('p');
            price.className = 'text-xs text-[#A18B81] mt-1';
            price.textContent = formatMoney(item.price);

            information.append(name, price);

            const controls = document.createElement('div');
            controls.className = 'flex items-center gap-2';

            const decrease = document.createElement('button');
            decrease.type = 'button';
            decrease.textContent = '−';
            decrease.className =
                'w-7 h-7 rounded-lg bg-[#F8F0EC] text-[#76564B]';
            decrease.addEventListener('click', () => {
                updateQuantity(item.id, -1);
            });

            const quantity = document.createElement('span');
            quantity.className = 'text-sm font-semibold min-w-5 text-center';
            quantity.textContent = item.quantity;

            const increase = document.createElement('button');
            increase.type = 'button';
            increase.textContent = '+';
            increase.className =
                'w-7 h-7 rounded-lg bg-[#F8F0EC] text-[#76564B]';
            increase.addEventListener('click', () => {
                updateQuantity(item.id, 1);
            });

            controls.append(decrease, quantity, increase);
            wrapper.append(information, controls);
            cartItems.append(wrapper);

            const productIdInput = document.createElement('input');
            productIdInput.type = 'hidden';
            productIdInput.name = `items[${index}][product_id]`;
            productIdInput.value = item.id;

            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = `items[${index}][quantity]`;
            quantityInput.value = item.quantity;

            cartInputs.append(productIdInput, quantityInput);

            index++;
        });

        updateSummary();
    };

    const updateSummary = () => {
        const { subtotal, total } = calculateTotals();

        subtotalElement.textContent = formatMoney(subtotal);
        totalElement.textContent = formatMoney(total);

        const isCash = paymentMethod.value === 'cash';

        if (!isCash) {
            amountPaidInput.value = total;
            amountPaidInput.readOnly = true;
        } else {
            amountPaidInput.readOnly = false;
        }

        const amountPaid = toNumber(amountPaidInput.value);
        const change = isCash
            ? Math.max(0, amountPaid - total)
            : 0;

        changeElement.textContent = formatMoney(change);

        checkoutButton.disabled =
            cart.size === 0 ||
            total <= 0 ||
            amountPaid < total;
    };

    productCards.forEach((card) => {
        card.addEventListener('click', () => {
            addProduct(card);
        });
    });

    searchInput.addEventListener('input', () => {
        const keyword = searchInput.value.toLowerCase().trim();

        productCards.forEach((card) => {
            const name = card.dataset.name.toLowerCase();

            card.classList.toggle(
                'hidden',
                !name.includes(keyword)
            );
        });
    });

    clearButton.addEventListener('click', () => {
        cart.clear();
        renderCart();
    });

    discountInput.addEventListener('input', updateSummary);
    amountPaidInput.addEventListener('input', updateSummary);
    paymentMethod.addEventListener('change', updateSummary);

    checkoutForm.addEventListener('submit', (event) => {
        if (cart.size === 0) {
            event.preventDefault();
            return;
        }

        const { total } = calculateTotals();

        if (total <= 0 || toNumber(amountPaidInput.value) < total) {
            event.preventDefault();
            alert('Jumlah pembayaran belum mencukupi.');
            return;
        }

        checkoutButton.disabled = true;
        checkoutButton.textContent = 'Memproses...';
    });

    renderCart();
});
