Nouveau message depuis le portfolio

Nom : {{ $submission->name }}
E-mail : {{ $submission->email }}
Langue : {{ strtoupper($submission->locale) }}
Objet : {{ $submission->subject ?: 'Non précisé' }}

Message :
{{ $submission->message }}
