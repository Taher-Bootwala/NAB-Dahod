/* Client-side image compression before upload (admin panel).
   Resizes to a max dimension and re-encodes to WebP/JPEG to cut payload. */
window.compressImage = function compressImage(file, { maxDim = 1600, quality = 0.82, type = "image/webp" } = {}) {
  return new Promise((resolve, reject) => {
    if (!file || !file.type.startsWith("image/")) return resolve(file);
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = () => {
      URL.revokeObjectURL(url);
      let { width, height } = img;
      if (width > maxDim || height > maxDim) {
        const r = Math.min(maxDim / width, maxDim / height);
        width = Math.round(width * r); height = Math.round(height * r);
      }
      const c = document.createElement("canvas");
      c.width = width; c.height = height;
      const ctx = c.getContext("2d");
      ctx.drawImage(img, 0, 0, width, height);
      c.toBlob(blob => {
        if (!blob) return resolve(file);
        const ext = type === "image/webp" ? "webp" : "jpg";
        const name = file.name.replace(/\.[^.]+$/, "") + "." + ext;
        resolve(new File([blob], name, { type }));
      }, type, quality);
    };
    img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
    img.src = url;
  });
};
