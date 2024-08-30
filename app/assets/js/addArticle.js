document
  .querySelector("#posts_form_featuredImage")
  .addEventListener("change", checkFile);

function checkFile() {
  let preview = document.querySelector(".preview");
  let image = document.querySelector(".preview img");
  let file = this.files[0];
  const types = ["image/png", "image/jpeg", "image/webp"];
  let reader = new FileReader();
  console.log(file)

  reader.onloadend = function () {
    image.src = reader.result;
    preview.style.display = "block";
  };

  // On vérifie si le fichier existe
  if (file) {
    // On vérifie le type de l'image
    if (types.includes(file.type)) {
      reader.readAsDataURL(file);
    }
  } else {
    image.src = '';
    preview.style.display = "none";
  }
}
