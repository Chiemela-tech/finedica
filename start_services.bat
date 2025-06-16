@echo off
REM Start all required Python services for FINEDICA

REM Start chatbot service
start "Chatbot Service" cmd /k "cd ..\chatbot && python chatbot.py"

REM Start expenditure service (if needed)
REM start "Expenditure Service" cmd /k "cd ..\expenditure && python expenditure_app.py"

REM Add more services as needed

echo All services started. Leave these windows open to keep services running.
pause
