#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <windows.h>

// Serial port configuration
#define COM_PORT "COM5" // Replace with your Arduino's COM port
#define BAUD_RATE CBR_9600
#define OUTPUT_FILE "C:\\Users\\benzs\\Downloads\\uno\\serial.txt"

void error_exit(const char *message) {
    fprintf(stderr, "%s\n", message);
    exit(EXIT_FAILURE);
}

int main() {
    // Open the serial port
    HANDLE hSerial = CreateFile(
        COM_PORT,
        GENERIC_READ,
        0,
        NULL,
        OPEN_EXISTING,
        FILE_ATTRIBUTE_NORMAL,
        NULL
    );

    if (hSerial == INVALID_HANDLE_VALUE) {
        error_exit("Error: Unable to open serial port.");
    }

    // Configure the serial port
    DCB serialParams = {0};
    serialParams.DCBlength = sizeof(serialParams);

    if (!GetCommState(hSerial, &serialParams)) {
        CloseHandle(hSerial);
        error_exit("Error: Unable to get serial port state.");
    }

    serialParams.BaudRate = BAUD_RATE;
    serialParams.ByteSize = 8;
    serialParams.StopBits = ONESTOPBIT;
    serialParams.Parity = NOPARITY;

    if (!SetCommState(hSerial, &serialParams)) {
        CloseHandle(hSerial);
        error_exit("Error: Unable to configure serial port.");
    }

    // Set timeouts
    COMMTIMEOUTS timeouts = {0};
    timeouts.ReadIntervalTimeout = 50;
    timeouts.ReadTotalTimeoutConstant = 50;
    timeouts.ReadTotalTimeoutMultiplier = 10;

    if (!SetCommTimeouts(hSerial, &timeouts)) {
        CloseHandle(hSerial);
        error_exit("Error: Unable to set serial port timeouts.");
    }

    printf("Listening for UID...\n");

    char buffer[128];
    DWORD bytesRead;
    FILE *file;

    while (1) {
        // Read data from the serial port
        if (ReadFile(hSerial, buffer, sizeof(buffer) - 1, &bytesRead, NULL) && bytesRead > 0) {
            buffer[bytesRead] = '\0'; // Null-terminate the string

            // Check for "UID Detected:"
            char *uidStart = strstr(buffer, "UID Detected: ");
            if (uidStart) {
                uidStart += strlen("UID Detected: ");
                printf("UID Received: %s\n", uidStart);

                // Write UID to the file
                file = fopen(OUTPUT_FILE, "w");
                if (file) {
                    fprintf(file, "%s", uidStart);
                    fclose(file);
                    printf("UID written to %s\n", OUTPUT_FILE);
                } else {
                    fprintf(stderr, "Error: Unable to write to file.\n");
                }
            }
        }
        Sleep(100); // Small delay to avoid overloading the CPU
    }

    // Close the serial port
    CloseHandle(hSerial);
    return 0;
}
