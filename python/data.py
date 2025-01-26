import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
import sys
import pywhatkit as kit

usuario     = sys.argv[1]
clave       = sys.argv[2]
nombre      = sys.argv[3]
# telefono    = sys.argv[4]

# Configurar los parámetros del correo
remitente = "alejandro020215@gmail.com"
destinatario = usuario
asunto = "Bienvenido a nuestro sistema de gestión de mucuritas 2"
cuerpo = f"""
Hola {nombre},

Tu cuenta ha sido creada exitosamente. A continuación, te proporcionamos tus datos de inicio de sesión:
Usuario: {destinatario}
Clave: {clave}

Gracias por unirte a nuestro sitio. ¡Esperamos que disfrutes tu experiencia!

Saludos,
"""

# Configurar el servidor SMTP
servidor_smtp = "smtp.gmail.com"
puerto = 587
usuario_smtp = "estacmucuritas2@gmail.com"
clave_smtp = "mijx dyjg ppbf caej"

# Crear el objeto del mensaje
mensaje = MIMEMultipart()
mensaje["From"] = remitente
mensaje["To"] = destinatario
mensaje["Subject"] = asunto
mensaje.attach(MIMEText(cuerpo, "plain"))

# Iniciar la conexión con el servidor SMTP
try:
    with smtplib.SMTP(servidor_smtp, puerto) as servidor:
        servidor.starttls()
        servidor.login(usuario_smtp, clave_smtp)
        servidor.send_message(mensaje)
    print("Correo enviado exitosamente")
except Exception as e:
    print("Error al enviar el correo:", str(e))