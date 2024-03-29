<?php if (!defined("APPLICATION")) {
    exit();
}

// Misc
$Definition["1 year"] = "1 ano";
$Definition["3 months"] = "3 meses";
$Definition["5 years"] = "5 anos";
$Definition["6 months"] = "6 meses";
$Definition["Auto Award"] = "Prêmio Automatico";
$Definition["Automatically Award"] = "Ganhar Automaticamente";
$Definition["Award Value"] = "Valor do Prêmio";
$Definition["Css Class"] = "Classe de Css";
$Definition["Days"] = "Dias";
$Definition["Description"] = "Descrição";
$Definition["Export"] = "Exportar";
$Definition["Grant"] = "Conceder";
$Definition["Icon"] = "Icone";
$Definition["Image Files"] = "Arquivo de Imagens";
$Definition["Image"] = "Imagem";
$Definition["Less than:"] = "Menor que:";
$Definition["More than or:"] = "Maior que ou:";
$Definition["More than:"] = "Maior que:";
$Definition["Permission"] = "Permissão";
$Definition["Photo"] = "Foto";
$Definition["Rule"] = "Regra";
$Definition["Time Frame"] = "Periodo de Tempo";
$Definition["Tooltip"] = "Dica";
$Definition["Weeks"] = "Semanas";
$Definition["Yaga.Settings"] = "Configurações do Yaga";
$Definition["Years"] = "Anos";

// Actions
$Definition["Yaga.Action"] = "Ação";
$Definition["Yaga.Action.Add"] = "Adicionar Ação";
$Definition["Yaga.Action.Added"] = "Ação Adicionada com Sucesso!";
$Definition["Yaga.Action.Delete"] = "Deletar Ação";
$Definition["Yaga.Action.Edit"] = "Editar Ação";
$Definition["Yaga.Action.Invalid"] = "Ação Inválida";
$Definition["Yaga.Action.InvalidTargetID"] = "ID Inválida";
$Definition["Yaga.Action.InvalidTargetType"] = "Alvo de Reação Inválida";
$Definition["Yaga.Action.Move"] = "Mover as %s reações?";
$Definition["Yaga.Action.PermDesc"] =
    "Um usuário precisará da seguinte permissão para usar essa ação. O padrão é 'Yaga.Reactions.Add'.";
$Definition["Yaga.ActionUpdated"] = "Ação Atualizada com Sucesso!";
$Definition["Yaga.Actions.Current"] = "Ações Atuais";
$Definition["Yaga.Actions.Desc"] =
    "As ações são mostradas embaixo do Conteúdo Gerado pelo Usuário, tais como discussões, comentários e artigos de atividade. Outros usuários podem selecionar uma 'reação'. O proprietário do item original receberá pontos com base nas reações dos outros. Isso forma um ciclo de feedback positivo para ambas as ações positivas <em>e</em> negativas.";
$Definition["Yaga.Actions.Manage"] = "Gerencias Ações";
$Definition["Yaga.Actions.Settings.Desc"] =
    "Aqui você pode gerenciar as ações disponíveis que podem ser usadas ​​como reações. Arraste os itens para classificar a ordem de exibição.";
$Definition["Yaga.Reactions"] = "Reações";
$Definition["Yaga.Reactions.RecordFormat"] = "%s - %s no %s.";
$Definition["Yaga.Reactions.RecordLimit.Plural"] = "e %s outros.";
$Definition["Yaga.Reactions.RecordLimit.Single"] = "e %s outros.";
$Definition["Yaga.Reactions.Use"] = "Usar Reações";

// Badges
$Definition["Yaga.Badge"] = "Emblema";
$Definition["Yaga.Badge.Add"] = "Adicionar Emblema";
$Definition["Yaga.Badge.Added"] = "Emblema atualizado com sucesso!";
$Definition["Yaga.Badge.AlreadyAwarded"] = "%s já possue este emblema!";
$Definition["Yaga.Badge.Award"] = "Dar Emblema";
$Definition["Yaga.Badge.Delete"] = "Deletar Emblema";
$Definition["Yaga.Badge.DetailLink"] = "Ver Estatísticas sobre este Emblema";
$Definition["Yaga.Badge.Earned"] = "Você ganhou este Emblema";
$Definition["Yaga.Badge.Earned.Format"] =
    "Você ganhou este Emblemas as %s do %s";
$Definition["Yaga.Badge.EarnedByNone"] = "Ninguem ganhou este Emblema ainda.";
$Definition["Yaga.Badge.EarnedByPlural"] = "%s pessoas ganharam este Emblema.";
$Definition["Yaga.Badge.EarnedBySingle"] = "%s pessoa ganhou este Emblema.";
$Definition["Yaga.Badge.EarnedHeadlineFormat"] =
    '{ActivityUserID,You} ganhou o emblema <a href="{Url,html}">{Data.Name,text}</a>.';
$Definition["Yaga.Badge.Edit"] = "Editar Emblema";
$Definition["Yaga.Badge.GiveTo"] = "Dar um Emblema a %s";
$Definition["Yaga.Badge.PhotoDeleted"] = "A Foto do Emblema foi Deletada.";
$Definition["Yaga.Badge.Reason"] = "Razão (opcional)";
$Definition["Yaga.Badge.RecentRecipients"] = "Ganhadores mais recentes";
$Definition["Yaga.Badge.Updated"] = "Emblema atualizado com sucesso!";
$Definition["Yaga.Badge.View"] = "Ver Emblema: ";
$Definition["Yaga.Badges"] = "Emblemas";
$Definition["Yaga.Badges.All"] = "Todos os Emblamas";
$Definition["Yaga.Badges.Desc"] =
    "Emblemas são concedidos aos usuários que atendem aos critérios definidos pelas regras associadas. São registrados em seu perfil e também por pontos. Eles podem ser usados ​​para criar um sistema de conquista que reforça o bom comportamento do usuário.";
$Definition["Yaga.Badges.Manage"] = "Gerenciar Emblemas";
$Definition["Yaga.Badges.Mine"] = "Meus Emblemas";
$Definition["Yaga.Badges.Notify"] = "Me notificar quando eu ganhar um Emblema.";
$Definition["Yaga.Badges.Settings.Desc"] =
    "Você pode gerenciar os emblemas disponíveis aqui. Emblemas desativadas não serão concedidos automaticamente.";
$Definition["Yaga.Badges.Use"] = "Usar Emblemas";

// Best Content
$Definition["Yaga.BestContent"] = "O Melhor...";
$Definition["Yaga.BestContent.Action"] = "Melhor %s Conteúdo";
$Definition["Yaga.BestContent.AllTime"] = "Melhor Conteúdo de Todos os Tempos";
$Definition["Yaga.BestContent.Recent"] = "Melhores Conteúdos Recentes";

// Errors
$Definition["Yaga.Error.AddFile"] = "Não é possível adicionar arquivo: %s";
$Definition["Yaga.Error.ArchiveChecksum"] =
    "Arquivo parece estar corrompido: Checksum é inválido.";
$Definition["Yaga.Error.ArchiveCreate"] = "Não é possível criar o arquivo: %s";
$Definition["Yaga.Error.ArchiveExtract"] = "Não é possível extrair o arquivo.";
$Definition["Yaga.Error.ArchiveOpen"] = "Não é possível abrir o arquivo.";
$Definition["Yaga.Error.ArchiveSave"] = "Não é possivel Salvar o arquivo: %s";
$Definition["Yaga.Error.DeleteFailed"] = "Falhou ao Deletar %s";
$Definition["Yaga.Error.FileDNE"] = "Arquivo não exite.";
$Definition["Yaga.Error.Includes"] =
    "Você deve selecionar pelo menos um item para o transportar.";
$Definition["Yaga.Error.NeedJS"] = "Isto deve ser feito via Javascript";
$Definition["Yaga.Error.NoActions"] = "Não há ações difinidas.";
$Definition["Yaga.Error.NoBadges"] =
    "Você não pode prêmiar emblemas sem defini-los.";
$Definition["Yaga.Error.NoRanks"] =
    "Você não pode promover usuários sem ter definido Emblemas.";
$Definition["Yaga.Error.NoRules"] =
    "Você não pode Adicionar ou Editar Emblemas sem regras!";
$Definition["Yaga.Error.ReactToOwn"] =
    "Você não pode reagir em su próprio comteúdo.";
$Definition["Yaga.Error.Rule404"] = "Regra não encontrada.";
$Definition["Yaga.Error.TransportCopy"] =
    "Não é possivel copiar arquivo de imagem.";
$Definition["Yaga.Error.TransportRequirements"] =
    "Você não parece ter os requisitos mínimos para o transportar de uma configuração Yaga automaticamente. Por favor, veja o manual_transport.md para mais informações.";

// Leader Board
$Definition["Yaga.LeaderBoard.AllTime"] = "Liders de todos os tempos ";
$Definition["Yaga.LeaderBoard.Max"] = "Número máximo de líderes para mostrar";
$Definition["Yaga.LeaderBoard.Month"] = "Lider deste mês";
$Definition["Yaga.LeaderBoard.Use"] =
    "Mostrar classificação na página de atividade";
$Definition["Yaga.LeaderBoard.Week"] = "Liders desta semana";
$Definition["Yaga.LeaderBoard.Year"] = "Liders deste ano";

// Perks
$Definition["Yaga.Perks"] = "Previlégios";
$Definition["Yaga.Perks.Curation"] = "Curadoria de Conteúdo";
$Definition["Yaga.Perks.EditTimeout"] = "Tempo de Edição";
$Definition["Yaga.Perks.Emoticons"] = "Formatar Emoticons";
$Definition["Yaga.Perks.MeActions"] = "Formatar Ações /me";
$Definition["Yaga.Perks.Signatures"] = "Editar Sinatura";
$Definition["Yaga.Perks.Tags"] = "Adicionar Tags";

// Ranks
$Definition["Yaga.Rank"] = "Posição";
$Definition["Yaga.Rank.Add"] = "Adicionar Posição";
$Definition["Yaga.Rank.Added"] = "Posição adicionada com sucesso!";
$Definition["Yaga.Rank.Delete"] = "Deletar Posição";
$Definition["Yaga.Rank.Edit"] = "Editar Posição";
$Definition["Yaga.Rank.Photo.Desc"] =
    "Esta foto será mostrada em mensagens de atividade e em notificações relativas a progressão da posição.";
$Definition["Yaga.Rank.PhotoDeleted"] = "Foto da Posição deletada com sucesso.";
$Definition["Yaga.Rank.PhotoUploaded"] =
    "Foto da Posição atualizada com sucesso!";
$Definition["Yaga.Rank.Progression"] = "Progresso de Posição";
$Definition["Yaga.Rank.Progression.Desc"] =
    "Permitir o usuário progredir automaticamente após esta Posição.";
$Definition["Yaga.Rank.Promote"] = "Editar Posição";
$Definition["Yaga.Rank.Promote.Format"] = "Editar Posição do %s";
$Definition["Yaga.Rank.PromotedHeadlineFormat"] =
    "{ActivityUserID,You} ganhou uma promoção para {Data.Name,text}.";
$Definition["Yaga.Rank.RecordActivity"] =
    "Gravar esta atualização de posição no log de atividade.";
$Definition["Yaga.Rank.Updated"] = "Posição atualizada com sucesso!";
$Definition["Yaga.Ranks"] = "Posições";
$Definition["Yaga.Ranks.AgeReq"] = "Tempo Requirido";
$Definition["Yaga.Ranks.Desc"] =
    "Posições são concedidas aos usuários com base na idade de sua conta e pontos acumulados. Posições têm vantagens associadas que podem ser utilizados para alterar a experiência do usuário.";
$Definition["Yaga.Ranks.Manage"] = "Gerenciar Posições";
$Definition["Yaga.Ranks.Notify"] = "Me notificar quando for promovido.";
$Definition["Yaga.Ranks.PointsReq"] = "Pontos Requiridos";
$Definition["Yaga.Ranks.PostsReq"] = "Postagens Requiridas";
$Definition["Yaga.Ranks.RequiredAgeDNC"] = "A Conta pode ter qualquer idade";
$Definition["Yaga.Ranks.RequiredAgeFormat"] =
    "A conta tem que ter %s de atividade.";
$Definition["Yaga.Ranks.Settings.Desc"] =
    "Aqui você pode gerenciar as posições disponíveis. Posições desativadas não serão concedidas automaticamente. Arraste os itens para classificar a ordem de promoção.";
$Definition["Yaga.Ranks.Use"] = "Usar Posições";

// Rules
$Definition["Yaga.Rules.AwardCombo"] = "Prêmio Combo";
$Definition["Yaga.Rules.AwardCombo.Criteria.Head"] =
    "Número de Tipos de Emblema";
$Definition["Yaga.Rules.AwardCombo.Desc"] =
    "Prêmie este emblema se a contagem de emblemas únicos (com base na regra) que um usuário recebeu dentro de prazo determinado.";
$Definition["Yaga.Rules.CakeDayPost"] = "Post de Aniversário";
$Definition["Yaga.Rules.CakeDayPost.Desc"] =
    "Premie estem emblema se o usuário postou no dia de aniversário de sua conta";
$Definition["Yaga.Rules.CommentCount"] = "Total de Comentários";
$Definition["Yaga.Rules.CommentCount.Criteria.Head"] = "Total de Comentários";
$Definition["Yaga.Rules.CommentCount.Desc"] =
    "Premie este emblema se o usuário atingiu um determinado numero de comentários.";
$Definition["Yaga.Rules.CommentMarathon"] = "Maratona de Comentários";
$Definition["Yaga.Rules.CommentMarathon.Criteria.Head"] =
    "Numero de Comentários";
$Definition["Yaga.Rules.CommentMarathon.Desc"] =
    "Premie este emblema se o usuário fez um numero determinado de comentários em um periodo de tempo.";
$Definition["Yaga.Rules.DiscussionBodyLength"] = "Comprimeto";
$Definition["Yaga.Rules.DiscussionBodyLength.Criteria.Head"] =
    "Quantos caractéres?";
$Definition["Yaga.Rules.DiscussionBodyLength.Desc"] =
    "Premie este emblema caso a discussão de um usuário atinja um determinado numero de caracteres. Certifique-se de que você insira um número menor ou igual a %s.";
$Definition["Yaga.Rules.DiscussionCategory"] = "Discussão na categoria";
$Definition["Yaga.Rules.DiscussionCategory.Criteria.Head"] =
    "Selecione a Categoria:";
$Definition["Yaga.Rules.DiscussionCategory.Desc"] =
    "Premie este emblema se um usuário iniciou uma discussão em uma determinada categoria.";
$Definition["Yaga.Rules.DiscussionCount"] = "Total de Discussões";
$Definition["Yaga.Rules.DiscussionCount.Criteria.Head"] = "Total de Discussões";
$Definition["Yaga.Rules.DiscussionCount.Desc"] =
    "Premie este emblema caso o usuário tenha iniciado um determinado numero de discussões.";
$Definition["Yaga.Rules.DiscussionPageCount"] = "Quantidade de Páginas";
$Definition["Yaga.Rules.DiscussionPageCount.Criteria.Head"] =
    "Quantas páginas?";
$Definition["Yaga.Rules.DiscussionPageCount.Desc"] =
    "Premie este emblema caso a discussão de um usuário atinja um determinado numero de páginas.";
$Definition["Yaga.Rules.HasMentioned"] = "Mencionado";
$Definition["Yaga.Rules.HasMentioned.Desc"] =
    "Premie um usuário caso ele mencione alguem no formato `@usuário`.";
$Definition["Yaga.Rules.HolidayVisit"] = "Visita de Feriado";
$Definition["Yaga.Rules.HolidayVisit.Criteria.Head"] = "Data do feriado";
$Definition["Yaga.Rules.HolidayVisit.Desc"] =
    "Premie o usuário caso ele visite o forum em uma determinada data.";
$Definition["Yaga.Rules.LengthOfService"] = "Tempo de Serviço";
$Definition["Yaga.Rules.LengthOfService.Criteria.Head"] = "Tempo Servido";
$Definition["Yaga.Rules.LengthOfService.Desc"] =
    "Premie este emblema caso o usuário tenha dias, meses ou anos ativo.";
$Definition["Yaga.Rules.ManualAward"] = "Prêmio Manual";
$Definition["Yaga.Rules.ManualAward.Desc"] =
    "Este emblema <strong>nunca</strong> será ganho <em>automaticamente</em>. Use para emblemas exclusivas.";
$Definition["Yaga.Rules.NecroPost"] = "Reviva a Discussão";
$Definition["Yaga.Rules.NecroPost.Criteria.Head"] =
    "A quanto tempo a discussão esta morta?";
$Definition["Yaga.Rules.NecroPost.Desc"] =
    "Premie este emblema caso o usuário poste em uma discussão morta.";
$Definition["Yaga.Rules.NewbieComment"] =
    "Comente na discussão de um usuário novo";
$Definition["Yaga.Rules.NewbieComment.Criteria.Head"] = "Noobesa do usuário";
$Definition["Yaga.Rules.NewbieComment.Desc"] =
    "Premie este emblema caso o usuário comente na discussão do novato.";
$Definition["Yaga.Rules.PhotoExists"] = "Usuário tem Avatar";
$Definition["Yaga.Rules.PhotoExists.Desc"] =
    "Premie este emblema caso o usuário tenha colocado uma foto de perfil.";
$Definition["Yaga.Rules.PostCount"] = "Total de Posts";
$Definition["Yaga.Rules.PostCount.Criteria.Head"] = "Total de Posts";
$Definition["Yaga.Rules.PostCount.Desc"] =
    "Premie este emblema caso o usuário atinja um numero determinado de comentários e/ou discussões .";
$Definition["Yaga.Rules.PostReactions"] = "Reação de Posts";
$Definition["Yaga.Rules.PostReactions.Criteria.Head"] = "Quantidade de Reações";
$Definition["Yaga.Rules.PostReactions.Desc"] =
    "Premie este emblema caso o comentário de um usuario atinja um numero determinado de reações.";
$Definition["Yaga.Rules.PostReactions.LabelFormat"] = "# de %s's:";
$Definition["Yaga.Rules.QnAAnserCount"] =
    "Resposta Aceitas (Plugin QnA Requirido)";
$Definition["Yaga.Rules.QnAAnserCount.Criteria.Head"] =
    "Quantas Resposta aceitas?";
$Definition["Yaga.Rules.QnAAnserCount.Desc"] =
    "Premie este emblema caso o usuário tenha aceitado um numero determinado de respostas.";
$Definition["Yaga.Rules.ReactionCount"] = "Reações Totais";
$Definition["Yaga.Rules.ReactionCount.Criteria.Head"] = "Total de Reações";
$Definition["Yaga.Rules.ReactionCount.Desc"] =
    "Premie este emblema caso o usuário tenha recebido um numero determinado de reações.";
$Definition["Yaga.Rules.ReflexComment"] =
    "Comentar em uma Nova Discussão Rapidamente";
$Definition["Yaga.Rules.ReflexComment.Criteria.Head"] =
    "Segundos para Comentar";
$Definition["Yaga.Rules.ReflexComment.Desc"] =
    "Premie este emblema caso o usuário pode x segundos após a criação da discussão.";
$Definition["Yaga.Rules.SocialConnection"] = "Conecções Sociais";
$Definition["Yaga.Rules.SocialConnection.Criteria.Head"] = "Qual Rede Social?";
$Definition["Yaga.Rules.SocialConnection.Desc"] =
    "Premie este emblema caso o usuário conecte a uma determinada rede social.";

// Transport
$Definition["Yaga.Export"] = "Exportar Configuração do Yaga";
$Definition["Yaga.Export.Desc"] =
    "Você pode exportar a configuração existente do Yaga para fins de backup ou de transporte. Selecione quais seções de sua configuração Yaga devem ser exportados.";
$Definition["Yaga.Export.Success"] =
    "Sua configuração Yaga foi exportada com sucesso para: <strong>%s</strong>";
$Definition["Yaga.Import"] = "Importar Configuração do Yaga";
$Definition["Yaga.Import.Desc"] =
    "Você pode importa a configuração para <strong>substituir</strong> sua configuração atual. Selecione quais seções de sua configuração do Yaga devem ser <strong>substituidas</strong>.";
$Definition["Yaga.Import.Success"] =
    "Você substituiu com êxito a configuração do Yaga com o conteúdo do: <strong>%s</strong>";
$Definition["Yaga.Transport"] = "Importar / Exportar Configuração";
$Definition["Yaga.Transport.Desc"] =
    "Você pode usar esta ferramenta para facilitar o transporte de sua configuração do Yaga em sites com uma unica transferência de arquivos.";
$Definition["Yaga.Transport.File"] = "Transporte de Arquivo Yaga";
$Definition["Yaga.Transport.Return"] =
    "Voltar à página principal de configurações do Yaga.";
