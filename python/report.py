import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.base import MIMEBase
from email import encoders
import sys
import os

nombre  = sys.argv[1]
tlf     = sys.argv[2]
img     = sys.argv[3] if len(sys.argv) > 3 else None
detail  = sys.argv[4]

# Configurar los parámetros del correo
remitente = "alejandro020215@gmail.com"
destinatario = "alejandro020215@gmail.com"
asunto = "Información de problema sistema de gestión de mucuritas 2"
cuerpo = f"""
Hola,

El usuario {nombre} informa que tiene un problema:

Número de teléfono:
- {tlf}
Detalle del problema:
- {detail}

Saludos,
"""

# Configurar el servidor SMTP
servidor_smtp = "smtp.gmail.com"
puerto = 587
usuario_smtp = "alejandro020215@gmail.com"
clave_smtp = "teld crls abux ycxa"

# Crear el objeto del mensaje
mensaje = MIMEMultipart()
mensaje["From"] = remitente
mensaje["To"] = destinatario
mensaje["Subject"] = asunto
mensaje.attach(MIMEText(cuerpo, "plain"))

# Adjuntar la imagen si existe
if img and os.path.exists(img):
    with open(img, "rb") as archivo:
        adjunto = MIMEBase("application", "octet-stream")
        adjunto.set_payload(archivo.read())
        encoders.encode_base64(adjunto)
        adjunto.add_header("Content-Disposition", f"attachment; filename={os.path.basename(img)}")
        mensaje.attach(adjunto)

# Iniciar la conexión con el servidor SMTP
try:
    with smtplib.SMTP(servidor_smtp, puerto) as servidor:
        servidor.starttls()
        servidor.login(usuario_smtp, clave_smtp)
        servidor.send_message(mensaje)
    print("Correo enviado exitosamente")
except Exception as e:
    print("Error al enviar el correo:", str(e))