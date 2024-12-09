const zoomContainer = document.querySelector(".image-container");
const image = zoomContainer.querySelector("img");
zoomContainer.addEventListener("mousemove", (event) => {
    const rect = zoomContainer.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    const xPercent = (x / rect.width) * 100;
    const yPercent = (y / rect.height) * 100;
    image.style.transformOrigin = `${xPercent}% ${yPercent}%`;
});
zoomContainer.addEventListener("mouseleave", () => {
    image.style.transformOrigin = "center center";
});



const addButton = document.querySelector(".qty .after");
const numberInput = document.querySelector(".qty input");
addButton.addEventListener("click", (event) => {
    const max = parseInt(numberInput.max) || Infinity;
    newValue = (parseInt(numberInput.value) || 0)+1;
    numberInput.value = Math.min(newValue, max);
});
const subButton = document.querySelector(".qty .before");
subButton.addEventListener("click", (event) => {
    const min = parseInt(numberInput.min) || 0;
    newValue = (parseInt(numberInput.value) || 0)-1;
    numberInput.value = Math.max(newValue, min);
});
