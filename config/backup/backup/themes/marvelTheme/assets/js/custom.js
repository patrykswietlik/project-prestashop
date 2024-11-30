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


