
#include <SPI.h>
#include <Ethernet.h>
#include <TextFinder.h>

//int ledpin = 13;
int relaypin1 = 6;
int relaypin2 = 7;
char etat1 = '0';
char etat2 ='0';
//char etatLed = '0';
String str;
String securecode;

byte mac[] = { 
  0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xAD }; //physical mac address
byte ip[] = { 
  10, 255, 1, 2 }; // ip of arduino in lan
byte gateway[] = { 
  10, 255, 1, 1 }; // internet access via router
byte subnet[] = { 
  255, 255, 255, 0 }; //subnet mask
EthernetServer server(80); //server port

String readString; 

void setup()
{
  Ethernet.begin(mac, ip, gateway, subnet);
  Serial.begin(9600);
  pinMode(relaypin1, OUTPUT);
  pinMode(relaypin2, OUTPUT);
  etat1 = '0';
  etat2 ='0';
  securecode = "1234567890";
}

void loop()
{

  // Create a client connection
  EthernetClient client = server.available();
  if (client) {
    TextFinder finder(client);
    while (client.connected()) {
      if (client.available()) {
        char c = client.read();
        //read char by char HTTP request
        if (readString.length() < 100)
        {
          //store characters to string
          readString += c;
        }
        Serial.print(c);
        if (c == '\n') {
          if (readString.indexOf("?") <0)
          {
            //do nothing
          }
          else if(readString.indexOf("code=" + securecode) >0)
          {
            if(readString.indexOf("1=on") >0)
            {
              pinEtat(relaypin1,'1', &etat1 );
            }
            else if(readString.indexOf("1=off") >0)
            {
              pinEtat(relaypin1,'0', &etat1 );
            }
            else if(readString.indexOf("2=on") >0)
            {
              pinEtat(relaypin2,'1', &etat2 );
            }
            else if(readString.indexOf("2=off") >0)
            {
              pinEtat(relaypin2,'0', &etat2 );
            }
           statusAll();
           client.println(str);
          }
          else
          {
              client.println("");
          }
          //clearing string for next read
          readString="";
          //stopping client
          client.stop();
        }
      }
    }
  }
}
void pinEtat(int pin, char mode, char* etat )
{
	if(mode == '1')
	{
		digitalWrite(pin, HIGH);
		*etat = '1';
	}
	else
	{
		digitalWrite(pin, LOW);
		*etat = '0';
	}
}

void statusAll()
{
  str = "{ \n";
  str += "\trelay1 : ";
  str += etat1 ;
  str += " ,\n";
  str += "\trelay2 : ";
  str += etat2; 
  str += " \n";
  str += "}";
}
