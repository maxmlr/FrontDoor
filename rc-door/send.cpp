/*
 Usage: ./send <type> <code>
 Type is of [1=binary,2=key]
 */

#include "RCSwitch.h"
#include <stdlib.h>
#include <stdio.h>

int main(int argc, char *argv[]) {
    
    /*
     output PIN is hardcoded for testing purposes
     see https://projects.drogon.net/raspberry-pi/wiringpi/pins/
     for pin mapping of the raspberry pi GPIO connector
     */
    int PIN = 27;
    
    /*
     export the configured PIN using gpio utility supplied by wiringPi
     this enables the send command to be called without sudo 
    */
    system("gpio export 27 out");

    int type = atoi(argv[1]);
    
    if (wiringPiSetupSys () == -1) return 1;
	RCSwitch mySwitch = RCSwitch();
	mySwitch.enableTransmit(PIN);
    
    switch(type) {
        case 1:
        {
            char* code = argv[2];
            printf("sending [%s]\n", code);
            mySwitch.sendTriState(code);
            break;
        }
        case 2:
        {
            int code = atoi(argv[2]);
            printf("sending [%i]\n", code);
            mySwitch.send(code, 24);
            break;
        }
        default:
            printf("type[%i] is unsupported\n", type);
            return -1;
    }
	return 0;
}
