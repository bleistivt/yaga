<?php if (!defined("APPLICATION")) {
    exit();
}

// Divers
$Definition["1 year"] = "1 an";
$Definition["3 months"] = "3 mois";
$Definition["5 years"] = "5 ans";
$Definition["6 months"] = "6 mois";
$Definition["Auto Award"] = "Assignation auto.";
$Definition["Automatically Award"] = "Assigner automatiquement";
$Definition["Award Value"] = "Valeur de la récompense";
$Definition["Css Class"] = "Classe Css";
$Definition["Days"] = "Jours";
$Definition["Description"] = "Description";
$Definition["Export"] = "Exporter";
$Definition["Grant"] = "Attribuer";
$Definition["Icon"] = "Icone";
$Definition["Image Files"] = "Fichiers images";
$Definition["Image"] = "Image";
$Definition["Less than:"] = "Moins de:";
$Definition["More than or:"] = "Supérieur ou égal à:";
$Definition["More than:"] = "Supérieur à:";
$Definition["Photo"] = "Photo";
$Definition["Rule"] = "Règle";
$Definition["Time Frame"] = "Écart temporel";
$Definition["Tooltip"] = "Notification";
$Definition["Weeks"] = "Semaines";
$Definition["Yaga.Settings"] = "Paramètres Yaga";
$Definition["Years"] = "Années";
$Definition["Yaga.MenuLinks.Show"] =
    "Afficher les liens dans le menu principal qui décrivent votre configuration YAGA";

// Actions
$Definition["Yaga.Action"] = "Action";
$Definition["Yaga.Action.Add"] = "Ajouter une action";
$Definition["Yaga.Action.Added"] = "Action ajoutée avec succès !";
$Definition["Yaga.Action.Delete"] = 'Supprimer l\'action';
$Definition["Yaga.Action.Edit"] = 'Editer l\'action';
$Definition["Yaga.Action.Invalid"] = "Action invalide";
$Definition["Yaga.Action.InvalidTargetID"] = "ID invalide";
$Definition["Yaga.Action.InvalidTargetType"] = "Cible invalide !";
$Definition["Yaga.Action.Move"] = "Déplacer les %s réactions?";
$Definition["Yaga.Action.PermDesc"] =
    "Un utilisateur aura besoin des permissions suivantes pour utiliser cette action. la permission par defaut est 'Yaga.Reactions.Add'.";
$Definition["Yaga.ActionUpdated"] = "Action mise à jour !";
$Definition["Yaga.Actions.Current"] = "Actions actuelles";
$Definition["Yaga.Actions.Desc"] =
    "Les actions sont affichées au dessous des contenus générés par les utilisateurs, comme des discussion, commentaires ou posts d'activités. Les autres utilisateurs peuvent réagir en choisissant une 'réaction' parmi celles proposées. L'auteur du contenu se verra crédité des points définis par les règles, selon le type de réaction. Ceci permet d'entretenir une volonté de qualité de rédaction pour les utilisateurs.";
$Definition["Yaga.Actions.Manage"] = "Gérer les actions";
$Definition["Yaga.Actions.Settings.Desc"] =
    "Içi, vous pouvez gérer la liste des actions pouvant être utilisées comme réactions. Faites glisser les items pour modifier leur classement.";
$Definition["Yaga.Reactions"] = "Réactions";
$Definition["Yaga.Reactions.RecordFormat"] = "%s - %s le %s.";
$Definition["Yaga.Reactions.RecordLimit.Plural"] = "et %s autres.";
$Definition["Yaga.Reactions.RecordLimit.Single"] = "et %s autres.";
$Definition["Yaga.Reactions.Use"] = "Réactions des utilisateurs";

// Badges
$Definition["Yaga.Badge"] = "Badge";
$Definition["Yaga.Badge.Add"] = "Ajouter un badge";
$Definition["Yaga.Badge.Added"] = "Badge ajouté avec succès !";
$Definition["Yaga.Badge.AlreadyAwarded"] = "%s a déjà ce badge !";
$Definition["Yaga.Badge.Award"] = "Attribuer le badge";
$Definition["Yaga.Badge.Delete"] = "Supprimer le badge";
$Definition["Yaga.Badge.DetailLink"] = "Voir les statistiques de ce badge";
$Definition["Yaga.Badge.Earned"] = "Vous avez reçu ce badge !";
$Definition["Yaga.Badge.Earned.Format"] =
    "Vous avez reçu ce badge le %s de la part de %s";
$Definition["Yaga.Badge.EarnedByNone"] = 'Personne n\'a encore reçu ce badge.';
$Definition["Yaga.Badge.EarnedByPlural"] = "%s personnes ont reçu ce badge.";
$Definition["Yaga.Badge.EarnedBySingle"] = "%s personne a reçu badge.";
$Definition["Yaga.Badge.EarnedHeadlineFormat"] =
    '{ActivityUserID,user} a reçu le badge <a href="{Url,html}">{Data.Name,text}</a>.';
$Definition["Yaga.Badge.Edit"] = "Editer le badge";
$Definition["Yaga.Badge.GiveTo"] = "Attribuer le badge à %s";
$Definition["Yaga.Badge.PhotoDeleted"] = 'L\'image du badge à été supprimée.';
$Definition["Yaga.Badge.Reason"] = "Raison (optionnel)";
$Definition["Yaga.Badge.RecentRecipients"] = "Derniers détenteurs";
$Definition["Yaga.Badge.Updated"] = "Badge mis à jour avec succès !";
$Definition["Yaga.Badge.View"] = "Voir le badge: ";
$Definition["Yaga.Badges"] = "Badges";
$Definition["Yaga.Badges.All"] = "Tous les badges";
$Definition["Yaga.Badges.Desc"] =
    "Les badges sont remis aux utilisateurs qui remplissent les critères définis par les règles. Ils sont ajoutés à leur profil et leur accordent des points. Ils peuvent ainsi être utilisés pour créer un système de récompenses qui favorisent les comportements vertueux.";
$Definition["Yaga.Badges.Manage"] = "Gérer les badges";
$Definition["Yaga.Badges.Mine"] = "Mes badges";
$Definition["Yaga.Badges.Notify"] = "Me notifier quand je reçois un badge.";
$Definition["Yaga.Badges.Settings.Desc"] =
    "Içi, vous pouvez gérer la liste des badges. Les badges désactivés ne seront pas assignés automatiquement.";
$Definition["Yaga.Badges.Use"] = "Utiliser les badges";

// Meilleur contenu
$Definition["Yaga.BestContent"] = "Meilleur contenu ...";
$Definition["Yaga.BestContent.Action"] = 'Les contenus "%s" ';
$Definition["Yaga.BestContent.AllTime"] =
    "Le meilleur contenu de tous les temps !";
$Definition["Yaga.BestContent.Recent"] = "Les meilleurs commentaires réçents";

// Erreurs
$Definition["Yaga.Error.AddFile"] = 'Impossible d\'ajouter le fichier: %s';
$Definition["Yaga.Error.ArchiveChecksum"] =
    'L\Archive semble corrompue: l\'empreinte est invalide.';
$Definition["Yaga.Error.ArchiveCreate"] = 'Impossible de créer l\'archive: %s';
$Definition["Yaga.Error.ArchiveExtract"] = 'Impossible d\'extraire le fichier.';
$Definition["Yaga.Error.ArchiveOpen"] = 'Impossible d\'ouvrir l\'archive.';
$Definition["Yaga.Error.ArchiveSave"] =
    'Impossible de sauvegarder l\'archive: %s';
$Definition["Yaga.Error.DeleteFailed"] = "Impossible de supprimer %s";
$Definition["Yaga.Error.FileDNE"] = 'Le fichier n\'existe pas.';
$Definition["Yaga.Error.Includes"] =
    "Vous devez selectionnez au moins un item à transporter.";
$Definition["Yaga.Error.NeedJS"] = "Ceci doit être fait via Javascript";
$Definition["Yaga.Error.NoActions"] = 'Il n\y a pas d\'actions définies.';
$Definition["Yaga.Error.NoBadges"] =
    "Vous ne pouvez pas attribuer de badges sans en avoir défini.";
$Definition["Yaga.Error.NoRanks"] =
    "Vous ne pouvez pas promouvoir un utilisateur sans avoir créé de rang.";
$Definition["Yaga.Error.NoRules"] =
    "Vous ne pouvez pas ajoutez ou éditer les badges sans avoir défini des règles !";
$Definition["Yaga.Error.ReactToOwn"] =
    "Vous ne pouvez pas réagir à votre propre contenu.";
$Definition["Yaga.Error.Rule404"] = "Règle non trouvée.";
$Definition["Yaga.Error.TransportCopy"] =
    "Impossible de copier le fichier image.";
$Definition["Yaga.Error.TransportRequirements"] =
    'Il semble que vous n\'avez pas tous les pré-requis pour transporter uneconfiguration Yaga automatiquement. Référez vous au manuel "manual_transport.md" pour plus  d\'information.';

// Leader Board
$Definition["Yaga.LeaderBoard.AllTime"] = "Les plus décorés";
$Definition["Yaga.LeaderBoard.Max"] = "Maximum de membres sur le leaderboard";
$Definition["Yaga.LeaderBoard.Month"] = "Les plus décorés ce mois-ci";
$Definition["Yaga.LeaderBoard.Use"] =
    'Afficher le leaderboard sur la page d\'activité';
$Definition["Yaga.LeaderBoard.Week"] = "Les plus décorés cette semaine";
$Definition["Yaga.LeaderBoard.Year"] = "Les plus décorés cette année";

// Titres
$Definition["Yaga.Perks"] = "Titres";
$Definition["Yaga.Perks.Curation"] = "Opération";
$Definition["Yaga.Perks.EditTimeout"] = "Durée effective";
$Definition["Yaga.Perks.Emoticons"] = "Formatter les émoticônes";
$Definition["Yaga.Perks.MeActions"] = "Formatter les réactions personnelles";
$Definition["Yaga.Perks.Signatures"] = "Éditer la signature";
$Definition["Yaga.Perks.Tags"] = "Ajouter des tags";

// Rangs
$Definition["Yaga.Rank"] = "Rang";
$Definition["Yaga.Rank.Add"] = "Ajouter un rang";
$Definition["Yaga.Rank.Added"] = "Rang ajouté avec succès !";
$Definition["Yaga.Rank.Delete"] = "Supprimer le rang";
$Definition["Yaga.Rank.Edit"] = "Éditer le rang";
$Definition["Yaga.Rank.Photo.Desc"] =
    'Cette image sera affichée sur le fil d\'activité et dans les notifications concernant les promotions.';
$Definition["Yaga.Rank.PhotoDeleted"] = "La photo de rang a été supprimée.";
$Definition["Yaga.Rank.PhotoUploaded"] =
    "La photo de rang à été mise en ligne avec succès !";
$Definition["Yaga.Rank.Progression"] = "Progression";
$Definition["Yaga.Rank.Progression.Desc"] =
    "Autoriser les utilisateurs à progresser automatiquement après ce rang.";
$Definition["Yaga.Rank.Promote"] = "Editer le rang";
$Definition["Yaga.Rank.Promote.Format"] = "Editer le rang de %s";
$Definition["Yaga.Rank.PromotedHeadlineFormat"] =
    "{ActivityUserID,You} avez été promu au rang de {Data.Name,text}.";
$Definition["Yaga.Rank.RecordActivity"] =
    'Enregistrer cette opération d\'édition dans le fil d\'activité public.';
$Definition["Yaga.Rank.Updated"] = "Rangs mis à jour avec succès !";
$Definition["Yaga.Ranks"] = "Rangs";
$Definition["Yaga.Ranks.AgeReq"] = "Age requis";
$Definition["Yaga.Ranks.All"] = "Tous les rangs";
$Definition["Yaga.Ranks.Desc"] =
    "Les rangs sont attribués aux utilisateurs suivant l'âge de leur compte, les points accumulés, et le nombre total de posts. Chaque  Ranks have associated perks that can be used to alter the user's experience.";
$Definition["Yaga.Ranks.Manage"] = "Gérer les rangs";
$Definition["Yaga.Ranks.Notify"] = "Me notifier quand je suis promu.";
$Definition["Yaga.Ranks.PointsReq"] = "Points requis";
$Definition["Yaga.Ranks.PostsReq"] = "Commentaires requis";
$Definition["Yaga.Ranks.RequiredAgeDNC"] = 'L\'âge du compte n\'importe pas';
$Definition["Yaga.Ranks.RequiredAgeFormat"] =
    "Le compte doit être plus vieux que %s.";
$Definition["Yaga.Ranks.Settings.Desc"] =
    "Içi, ous pouvez gérer les rangs disponibles.You can manage the available ranks here. Les rangs désactivés ne seont pas attribués automatiquement. Faites glissers les items pour modifier leur classement.";
$Definition["Yaga.Ranks.Use"] = "Utiliser les rangs";
$Definition["Yaga.Ranks.Story.Manual"] = "Ce rang est donné manuellement";
$Definition["Yaga.Ranks.Story.Auto"] = "Ce rang sera décerné automatiquement";
$Definition["Yaga.Ranks.Story.PostReq"] = "a %s posts";
$Definition["Yaga.Ranks.Story.PostAndPointReq"] = "%s points";
$Definition["Yaga.Ranks.Story.PointReq"] = "a %s points";
$Definition["Yaga.Ranks.Story.AgeReq"] = "est agé au moins de %s an(s)";
$Definition["Yaga.Ranks.Story.3Reqs"] =
    "Ce rank sera décerné une fois que votre compte sera %s, %s, and %s";
$Definition["Yaga.Ranks.Story.2Reqs"] =
    "Ce rank sera décerné une fois que votre compte sera %s %s and %s";
$Definition["Yaga.Ranks.Story.1Reqs"] =
    "Ce rank sera décerné une fois que votre compte sera %s %s";

// Rules
$Definition["Yaga.Rules.AwardCombo"] = "Combo de récompenses";
$Definition["Yaga.Rules.AwardCombo.Criteria.Head"] =
    "Nombre de badges différents";
$Definition["Yaga.Rules.AwardCombo.Desc"] =
    'Attribuer ce badge si le nombre de badges uniques reçu par l\'utilisateur atteint ou dépasse celui spécifié, dans l\'intervalle de temps défini.';
$Definition["Yaga.Rules.CakeDayPost"] = "Commenter le jour de son anniversaire";
$Definition["Yaga.Rules.CakeDayPost.Desc"] =
    'Attribuer ce badge si l\'utilisateur poste un commentaire le jour de son anniversaire.';
$Definition["Yaga.Rules.CommentCount"] = "Nombre total de commentaires";
$Definition["Yaga.Rules.CommentCount.Criteria.Head"] = "Total des commentaires";
$Definition["Yaga.Rules.CommentCount.Desc"] =
    'Attribuer ce badge si l\'utilisateur atteint ou dépasse le nombre de commentaires spécifié.';
$Definition["Yaga.Rules.CommentMarathon"] = "Marathon de commentaires";
$Definition["Yaga.Rules.CommentMarathon.Criteria.Head"] =
    "Nombre de commentaires";
$Definition["Yaga.Rules.CommentMarathon.Desc"] =
    'Attibuer ce badge si l\'utilisateur a rédigé un nombre de commentaires supérieur ou égal à celui spécifié, dans l\'intervalle de temps défini.';
$Definition["Yaga.Rules.DiscussionBodyLength"] = "Longueur de post";
$Definition["Yaga.Rules.DiscussionBodyLength.Criteria.Head"] =
    "Combien de caractères ?";
$Definition["Yaga.Rules.DiscussionBodyLength.Desc"] =
    'Attribuer ce badge si l\'utilisateur possède une disccussion qui atteint le nombre de caractère spécifiés. Régler ce nombre de manière à ce qu\'il soit inférieur à %s.';

$Definition["Yaga.Rules.DiscussionCategory"] = "Discussion dans la catégorie";
$Definition["Yaga.Rules.DiscussionCategory.Criteria.Head"] =
    "Selectionner une catégorie :";
$Definition["Yaga.Rules.DiscussionCategory.Desc"] =
    'Attribuer ce badge si l\'utilisateur possède une discussion dans la catégorie spécifiée.';
$Definition["Yaga.Rules.DiscussionCount"] = "Nombre total de discussions";
$Definition["Yaga.Rules.DiscussionCount.Criteria.Head"] =
    "Total des discussions";
$Definition["Yaga.Rules.DiscussionCount.Desc"] =
    'Attibuer ce badge si le nombre total de discussion que l\'utilisateur à crée atteint le nombre spécifié.';
$Definition["Yaga.Rules.DiscussionPageCount"] =
    "Nombre de pages dans la discussion";
$Definition["Yaga.Rules.DiscussionPageCount.Criteria.Head"] =
    "Combien de pages?";
$Definition["Yaga.Rules.DiscussionPageCount.Desc"] =
    'Décernez ce badge si l\'utilisateur a une discussion qui atteint le nombre de pages cible.';
$Definition["Yaga.Rules.DiscussionWordMention"] =
    "Le commentaire contient la chaîne de caractères ...";
$Definition["Yaga.Rules.DiscussionWordMention.Criteria.Head"] =
    "Chaîne de caractères";
$Definition["Yaga.Rules.DiscussionWordMention.Desc"] =
    "Le commentaire contient la chaîne de caractères spécifiée ci-dessous";
$Definition["Yaga.Rules.DiscussionPageCount.Criteria.Head"] =
    "Combien de pages ?";
$Definition["Yaga.Rules.DiscussionPageCount.Desc"] =
    'Attribuer ce badge si l\'utilsateur possède une discussion dont le nombre de page atteint celui spécifié.';
$Definition["Yaga.Rules.HasMentioned"] = "Mention";
$Definition["Yaga.Rules.HasMentioned.Desc"] =
    'Attribuer ce badge si l\'utilisateur mentionne quelqu\'un. Les mentions sont de la forme `@nom`.';
$Definition["Yaga.Rules.HolidayVisit"] = "Visite pendant les vacances";
$Definition["Yaga.Rules.HolidayVisit.Criteria.Head"] = "Date des vacances";
$Definition["Yaga.Rules.HolidayVisit.Desc"] =
    'Attribuer le badge si l\'utilisateur visite le forum durant le jour de l\'année spécifié.';
$Definition["Yaga.Rules.LengthOfService"] = "Temps de service";
$Definition["Yaga.Rules.LengthOfService.Criteria.Head"] = "Time Served";
$Definition["Yaga.Rules.LengthOfService.Desc"] =
    "Award this badge if the user's account is older than the specified number of days, weeks, or years.";
$Definition["Yaga.Rules.ManualAward"] = "Attribution manuelle";
$Definition["Yaga.Rules.ManualAward.Desc"] =
    "Ce badge ne sera <strong>jamais</strong> attribué <em>automatiquement</em>. Utiliser cette méthode pour les badges à attribuer manuellement.";
$Definition["Yaga.Rules.NecroPost"] = "Commenter une discussion morte.";
$Definition["Yaga.Rules.NecroPost.Criteria.Head"] =
    "Quelle durée avant de considérer une discussion comme morte ?";
$Definition["Yaga.Rules.NecroPost.Desc"] =
    "Attribuer ce badge pour tout commentaire sur une discussion morte";
$Definition["Yaga.Rules.NewbieComment"] =
    "Commenter une discussion d'un nouvel utilisateur";
$Definition["Yaga.Rules.NewbieComment.Criteria.Head"] =
    'Ancienneté de l\'utilisateur';
$Definition["Yaga.Rules.NewbieComment.Desc"] =
    'Attribuer ce badge si le commenatire est placé dans la discussion d\'un nouvel utilisateur.';
$Definition["Yaga.Rules.PhotoExists"] = 'L\'utilisateur à une photo de profil';
$Definition["Yaga.Rules.PhotoExists.Desc"] =
    'Attribuer ce badge si l\'utilisateur a ujouté une photo de profil.';
$Definition["Yaga.Rules.PostCount"] = "Nombre total de commentaires";
$Definition["Yaga.Rules.PostCount.Criteria.Head"] =
    "Nombre total de commentaires";
$Definition["Yaga.Rules.PostCount.Desc"] =
    'Attribuer ce badge si le nombre total de disuccions/commentaires de l\'utilisateur atteint ou dépasse le nombre fixé.';
$Definition["Yaga.Rules.PostReactions"] =
    "Nombre de réactions à des commentaires";
$Definition["Yaga.Rules.PostReactions.Criteria.Head"] = "Nombre de réactions";
$Definition["Yaga.Rules.PostReactions.Desc"] =
    'Attribuer ce badge si l\'utilisateur possède un commentaire avec la réaction spécifiée.';
$Definition["Yaga.Rules.PostReactions.LabelFormat"] = "# of %s's:";
$Definition["Yaga.Rules.QnAAnserCount"] =
    "Réponses acceptées (Plugin QnA requis)";
$Definition["Yaga.Rules.QnAAnserCount.Criteria.Head"] =
    "Combien de réponses acceptées ?";
$Definition["Yaga.Rules.QnAAnserCount.Desc"] =
    'Attribuer le badge si l\'utilisateur Award this badge if the user has accepted answers that fit the criteria.';
$Definition["Yaga.Rules.ReactionCount"] = "Nombre total de réactions";
$Definition["Yaga.Rules.ReactionCount.Criteria.Head"] =
    "Nombre total de réactions";
$Definition["Yaga.Rules.ReactionCount.Desc"] =
    'Attribuer ce badge si l\'utilsateur à reçu un nombre total x de réactions du type spécifié.';
$Definition["Yaga.Rules.ReflexComment"] =
    "Commenter une nouvelle discussion rapidement";
$Definition["Yaga.Rules.ReflexComment.Criteria.Head"] =
    "Temps en secondes mis pour commenter";
$Definition["Yaga.Rules.ReflexComment.Desc"] =
    "Attribuer ce badge si un commentaire est posté x secondes après la création de la discussion.";
$Definition["Yaga.Rules.SocialConnection"] = "Connection aux réseaux sociaux";
$Definition["Yaga.Rules.SocialConnection.Criteria.Head"] =
    "Quel réseau social ?";
$Definition["Yaga.Rules.SocialConnection.Desc"] =
    'Attribuer se badge quand l\'utilisateur se connecte au réseau social spécifié.';
$Definition["Yaga.Rules.UnknownRule"] = "Règle inconnue";
$Definition["Yaga.Rules.UnknownRule.Desc"] =
    'Ce badge ne sera <strong>jamais</strong> décerné <em>automatiquement</em>. Ce sera sélectionné si la règle sauvée dans la base de données n\'est plus disponible. C\'est le plus souvent dû à un plugin qui fournissait une règle et qui n\'est plus activé.';

// Transport
$Definition["Yaga.Export"] = "Exporter une configuration Yaga";
$Definition["Yaga.Export.Desc"] =
    "Vous pouvez exporter votre configuration Yaga pour la sauvegarder ou la transporter. Selectionnez les parties de cette configuration que vous voulez exporter.";
$Definition["Yaga.Export.Success"] =
    "Votre configuration Yaga a été exporter dans le fichier: <strong>%s</strong>";
$Definition["Yaga.Import"] = "Importer une configuration Yaga";
$Definition["Yaga.Import.Desc"] =
    "Vous pouvez importer une configuration Yaga pour <strong>remplacer</strong> votre configuration actuelle. Selectionner les sections de votre configuration actuelle qui doivent être <strong>remplacées</strong>.";
$Definition["Yaga.Import.Success"] =
    "Vous avez remplacé votre configuration Yaga avec le contenu de : <strong>%s</strong>";
$Definition["Yaga.Transport"] = "Importer / Exporter une configuration";
$Definition["Yaga.Transport.Desc"] =
    "Vous pouvez utiliser ces outils pour faciliter le transport de votre configuration Yaga entre vos différents sites par un simple transfert de fichier.";
$Definition["Yaga.Transport.File"] = "Fichier de transport Yaga";
$Definition["Yaga.Transport.Return"] =
    "Retour à la page principale des réglages Yaga.";
