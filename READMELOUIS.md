## 🚀 Fonctionnalités principales

### 👩🏫 Côté Admin
- Ajoue de tache commune / suppression / modification 
- Génération de QCM via une intelligence artificielle (gemini API).

### 🧑🎓 Côté Étudiant
- Ajouté des commentairs aux taches commune et validation
- Historique des tache commune.
- Accès aux QCM assignés par l'admin.
- Réponse aux QCM dans une interface simple.
- Calcul et enregistrement automatique du score.
 

## ✅ Fonctionnalités techniques
- Middlewares conditionnels pour les accès selon rôle (is_admin)*

## ⚠️ Bugs connus / Points à améliorer
- preumière tentative avec deepseek non concluante 
- Parfois L'IA génère un fichier json qui n'est pas correctement structurée, le code ne pouvant pas encoder je fichier
- la l'ai de ne renvoie rien

## 🔧 Ce qu'il reste à faire
-  Permettre l’édition des QCM (actuellement non modifiables)
- Permetre a l'admin de donné le qcm a des promotions
- Un meilleur style
- L'admin peut rajouter des thèmes
