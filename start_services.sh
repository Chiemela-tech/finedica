#!/bin/bash
# Start all required Python services for FINEDICA

# Start chatbot service
echo "Starting chatbot service..."
cd ../chatbot && nohup python3 chatbot.py &
cd -

# Start expenditure service (uncomment if needed)
# cd ../expenditure && nohup python3 expenditure_app.py &
# cd -

echo "All services started."
