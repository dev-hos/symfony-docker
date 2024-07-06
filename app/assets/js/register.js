// Variables booléennes
let pseudo = false;
let email = false;
let rgpd = false;
let pass = true;

// On charge les éléments du formulaire
document
  .querySelector("#registration_form_nickname")
  .addEventListener("input", checkPseudo);

document
  .querySelector("#registration_form_email")
  .addEventListener("input", checkEmail);

document
  .querySelector("#registration_form_agreeTerms")
  .addEventListener("input", checkRgpd);

// Fonctions pour vérifier la validité des champs
function checkPseudo() {
  pseudo = this.value.length > 2;
  checkAll();
}

function checkEmail() {
  // On utilise une expression régulière pour vérifier le format de l'email
  // const regex = /[^@ \t\r\n]+@[^@ \t\r\n]+\.[^@ \t\r\n]+/;
  const regex = new RegExp("\\S+@\\S+\\.\\S+"); // Simplifié pour une meilleure performance et une meilleure compréhension
  email = regex.test(this.value);
  checkAll();
}

function checkRgpd() {
  rgpd = this.checked;
  checkAll();
}

function checkAll() {
  document.querySelector("#submit-button").setAttribute("disabled", "disabled");
  if (pseudo && email && pass && rgpd) {
    document.querySelector("#submit-button").removeAttribute("disabled");
  }
}

const PasswordStrength = {
  STRENGTH_VERY_WEAK: "Très faible",
  STRENGTH_WEAK: "Faible",
  STRENGTH_MEDIUM: "Moyen",
  STRENGTH_STRONG: "Fort",
  STRENGTH_VERY_STRONG: "Très fort",
};

// On charge l'écouteur sur le champ de mot de passe
document
  .querySelector("#registration_form_plainPassword")
  .addEventListener("input", checkPassword);

function checkPassword() {
  const mdp = this.value;
  // on récupère l'élément d'affichage de l'entropie
  const entropyElement = document.querySelector("#entropy");
  // on évalue la force du mot de passe
  let entropy = evaluatePasswordStrength(mdp);
  entropyElement.classList.remove("text-red", "text-orange", "text-green");
  // on attribue la couleur en fonction de l'entropie
  switch (entropy) {
    case "Très faible":
      entropyElement.classList.add("text-red");
      pass = false;
      break;
    case "Faible":
      entropyElement.classList.add("text-red");
      pass = false;
      break;
    case "Moyen":
      entropyElement.classList.add("text-green");
      pass = false;
      break;
    case "Fort":
      entropyElement.classList.add("text-green");
      pass = true;
      break;
    case "Très fort":
      entropyElement.classList.add("text-green");
      pass = true;
      break;
    default:
      entropyElement.classList.add("text-red");
      pass = false;
  }

  entropyElement.textContent = entropy;
}

function evaluatePasswordStrength(password) {
  // calcule de la longueur du mdp
  const length = password.length;

  if (!length) {
    return PasswordStrength.STRENGTH_VERY_WEAK;
  }

  // on crée un objet qui contiendra les caractères et leur nombre
  let passwordChars = {};

  for (let index = 0; index < password.length; index++) {
    let charCode = password.charCodeAt(index);
    passwordChars[charCode] = (passwordChars[charCode] || 0) + 1;
  }
  console.log(passwordChars);

  // compter le nombre de caractères différents dans le mdp
  let chars = Object.keys(passwordChars).length;
  console.log(chars);
  // On initialise les variables des types de caractères
  let control = 0,
    digit = 0,
    upper = 0,
    lower = 0,
    symbol = 0,
    other = 0;

  for (let [chr, count] of Object.entries(passwordChars)) {
    chr = Number(chr);
    console.log(chr);
    if (chr < 32 || chr === 127) {
      // Caractère de contrôle
      control = 33;
    } else if (chr >= 48 && chr <= 57) {
      // Chiffres
      digit = 10;
    } else if (chr >= 65 && chr <= 90) {
      // Majuscules
      upper = 26;
    } else if (chr >= 97 && chr <= 122) {
      // Minuscules
      lower = 26;
    } else if (chr >= 128) {
      // Autres caractères
      other = 128;
    } else {
      // Symboles
      symbol = 33;
    }
  }

  // Calcul du pool des caractères
  let pool = control + digit + upper + lower + symbol + other;

  // Calcul de l'entropie
  let entropy = chars * Math.log2(pool) + (length - chars) * Math.log2(chars);

  if (entropy >= 120) {
    return PasswordStrength.STRENGTH_VERY_STRONG;
  } else if (entropy >= 100) {
    return PasswordStrength.STRENGTH_STRONG;
  } else if (entropy >= 80) {
    return PasswordStrength.STRENGTH_MEDIUM;
  } else if (entropy >= 60) {
    return PasswordStrength.STRENGTH_WEAK;
  } else {
    return PasswordStrength.STRENGTH_VERY_WEAK;
  }

  // Autre méthode de vérification de la force du mot de passe (à décommenter)

  // const password = this.value;

  // // si le mdp est vide, on lui attribue une très faible entropie
  // if(!password) {
  //     return PasswordStrength.STRENGTH_VERY_WEAK;
  // }

  // // si le mdp contient uniquement des caractères spéciaux, on lui attribue une très faible entropie

  // // si le mdp est trop court, on lui attribue une faible entropie
  // if(length < 8) {
  //     return PasswordStrength.STRENGTH_VERY_WEAK;
  // }

  // // si il y a plus de 5 caractères différents, on lui attribue une moyenne d'entropie
  // if(differentChars > 5) {
  //     return PasswordStrength.STRENGTH_WEAK;
  // }

  // // si il y a entre 3 et 5 caractères différents, on lui attribue une moyenne d'entropie
  // if(differentChars >= 3 && differentChars <= 5) {
  //     return PasswordStrength.STRENGTH_MEDIUM;
  // }

  // si il y a entre 1 et 2 caractères différent

  // calcule de la complexité
  // let complexity = 0;
  // if (length >= 8) complexity++;
  // if (/[A-Z]/.test(password)) complexity++;
}

// Autre méthode de vérification de la force du mot de passe (à décommenter)

//   const password = this.value;
//   let strength = PasswordStrength.STRENGTH_VERY_WEAK;

//   // Vérification des caractères spéciaux
//   if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
//     strength = Math.max(strength, PasswordStrength.STRENGTH_WEAK);
//   }

//   // Vérification de la longueur
//   if (password.length >= 8) {
//     strength = Math.max(strength, PasswordStrength.STRENGTH_MEDIUM);
//   }

//   // Vérification des majuscules et minuscules
//   if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
//     strength = Math.max(strength, PasswordStrength,
//         PasswordStrength.STRENGTH_STRONG);
//   }

//   // Vérification des chiffres
//   if (/\d/.test(password)) {
//     strength = Math.max(strength, PasswordStrength.STRENGTH_STRONG);
//   }

//   // Vérification des caractères accentués
//   if (/[\u00C0-\u00D6\u00D8-\u00DE\u00E0-\u00F6\u00F8-\u00FE]/.test(password)) {
//     strength = Math.max(strength, PasswordStrength.STRENGTH_VERY_STRONG);
//   }

//   document.querySelector("#password-strength").textContent = strength;
