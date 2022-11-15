# electronic-toll-DSRC
L'Agicom ha menato forte Autostrade Per l'Italia e queste sono le conseguenze

---
Software Engineer - Backend Test

You’ve been asked by a company that owns a motorway to build an automatic toll software
infrastructure for their users.

Users have to be registered in the system.

Each user can (chose A or B):
```
a) Buy one device which will be installed in the vehicle
b) Buy one or more devices which will be installed in their vehicles
```

Users can enter or exit the motorway from a network of 500 stations across the country.
To simplify let’s assume you can reach any destination from each station.

When you drive through each station the device communicates with the station and notifies
that you’re entering (or exiting) the motorway from that specific station.

The cost for the travel is (chose A or B):

```
a) a fix cost per KM based on the distance from station A to station B
b) a cost that can be custom configured for each segment of the motorway. A segment
is a path that connect two adjacent stations.
```

Chose A or B as your desired level of complexity and then:
```
1. Design the database scheme for the users / device
2. Design the database scheme for the network of stations and a way to store or
    calculate the cost between all possible paths
3. Design the database scheme for the logs of the entrances/exits each vehicle will
    trigger when travelling through a station.
4. Write an API to store the logs of the entrance/exit on the database
5. Write the code to calculate the monthly amount due (for each customer the sum of
    all paths they drove in a given month) for
    a. A given customer
    b. Each customer
```
**Language: PHP**

Design, document and write the code.
Build a unit test for the whole system.

Thank you for your time.


