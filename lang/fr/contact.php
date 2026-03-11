<?php

return [
    // Page
    'page_title' => 'Nous Contacter - KDrama Hub',
    'title' => '📧 Nous Contacter',
    'subtitle' => 'Une question ou une suggestion ? Nous sommes à votre écoute !',

    // Info sections
    'info_reply_title' => '💬 Comment répondre ?',
    'info_reply_body' => 'Nous répondons personnellement à chaque message. Votre question est importante pour nous.',
    'info_time_title' => '⏱️ Temps de réponse',
    'info_time_body' => '24-48 heures',
    'info_time_note' => '(jours ouvrables)',

    // Form
    'form_title' => '📬 Envoyez-nous un message',
    'form_subtitle' => 'Remplissez le formulaire ci-dessous et nous vous répondrons rapidement.',
    'errors_title' => '⚠️ Erreurs dans le formulaire :',

    // Form fields
    'field_name' => 'Nom complet',
    'placeholder_name' => 'Jean Dupont',
    'field_email' => 'Adresse email',
    'placeholder_email' => 'jean@example.com',
    'field_subject' => 'Sujet',
    'placeholder_subject' => 'Ex: Suggestion de feature, Bug report, Partenariat...',
    'field_message' => 'Message',
    'placeholder_message' => 'Décrivez votre message en détails... (minimum 10 caractères)',
    'char_limit' => '/ 5000 caractères',
    'field_attachment' => '📎 Joindre un fichier',
    'optional' => '(optionnel)',
    'attachment_hint' => 'Max 5 MB • Formats: PDF, CSV, Excel, Images, Word',

    // Buttons
    'submit_button' => '📨 Envoyer le message',
    'back_btn' => '← Retour',
    'char_limit_label' => 'caractères',

    // Messages
    'success' => 'Merci ! Votre message a été reçu. Nous vous répondrons dans les plus brefs délais.',
    'error' => 'Une erreur s\'est produite. Veuillez réessayer plus tard.',

    // FAQ
    'faq_title' => '❓ Questions Fréquentes',
    'faq_1_title' => '⏰ Combien de temps pour une réponse ?',
    'faq_1_body' => 'Nous répondons généralement sous 24-48 heures (jours ouvrables). Les demandes urgentes peuvent être traitées plus rapidement.',
    'faq_2_title' => '📋 Quels sujets traitez-vous ?',
    'faq_2_body' => 'Suggestions de features, bugs, partenariats, questions générales - tous les sujets sont les bienvenus!',
    'faq_3_title' => '🐛 Comment signaler un bug ?',
    'faq_3_body' => 'Décrivez le problème en détail, la plateforme utilisée, et les étapes pour le reproduire. Joignez des screenshots si possible.',
    'faq_4_title' => '🔒 Confidentialité ?',
    'faq_4_body' => 'Vos données personnelles sont traitées confidentiellement et ne seront jamais partagées.',

    // Additional form labels
    'label_required' => '*',
    'char_limit_label' => 'caractères',
    'file_help' => 'Max 5 MB • Formats: PDF, CSV, Excel, Images, Word',
    'submit_btn' => '📨 Envoyer le message',
    'back_btn' => '← Retour',

    // FAQ
    'faq' => [
        'title' => '❓ Questions Fréquentes',
        'q1' => '⏰ Combien de temps pour une réponse ?',
        'a1' => 'Nous répondons généralement dans les 24-48 heures en semaine.',
        'q2' => '📋 Quels sujets traitez-vous ?',
        'a2' => 'Suggestions, bugs, partenariats, questions techniques et bien d\'autres.',
        'q3' => '🐛 Comment signaler un bug ?',
        'a3' => 'Détaillez le bug, l\'étape pour le reproduire et votre navigateur.',
        'q4' => '🔒 Confidentialité ?',
        'a4' => 'Vos données ne seront utilisées que pour vous répondre. Aucun partage tiers.',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Le nom est requis',
        'email_required' => 'L\'adresse email est requise',
        'email_invalid' => 'L\'adresse email n\'est pas valide',
        'subject_required' => 'Le sujet est requis',
        'message_required' => 'Le message est requis',
        'message_min' => 'Le message doit contenir au moins 10 caractères',
        'message_max' => 'Le message ne doit pas dépasser 5000 caractères',
        'attachment_max' => 'Le fichier ne doit pas dépasser 5 MB',
        'attachment_mimes' => 'Format de fichier non autorisé',
    ],
];
