<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Convite para Projeto</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f7; color: #333; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
        <tr>
            <td>
                <h3 style="color: #111827; font-size: 20px; margin-bottom: 10px;">Olá {{ $mailData['receiver_name'] }},</h3>

                <p style="font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
                  Foste convidado(a) para o projeto : <strong>{{ $mailData['project_name'] }}</strong>.
                </p>

                <p style="font-size: 16px; line-height: 1.5; margin-bottom: 20px;">Faz login no Atlas e clica no botão:</p>

                <a href="{{ $mailData['accept_url'] }}" style="display: inline-block; padding: 12px 24px; background-color: #724e90ff; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 8px; font-size: 16px;">Aceitar convite</a>

                <p style="font-size: 14px; line-height: 1.5; margin-top: 30px; color: #6b7280;">
                   Se não te quiseres juntar , basta eliminares este email.
                </p>

                <p style="font-size: 14px; line-height: 1.5; margin-top: 20px; color: #6b7280;">
                   <br>
                    Equipa Atlas 
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
