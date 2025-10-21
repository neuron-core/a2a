# A2A PHP server

This project is about building a PHP server to allow AI Agents to communicate with other agents using the A2A protocol.

The A2A protocol is an open standard that enables seamless communication and collaboration between AI agents. 
It provides a common language for agents built using diverse frameworks and by different vendors, fostering 
interoperability and breaking down silos. Agents are autonomous problem-solvers that act independently within their environment. 
A2A allows agents from different developers, built on different frameworks, and owned by different organizations to unite and work together.

Here is the link to the specifications: https://a2a-protocol.org/latest/specification/

## Scope of the project

The server will be built in PHP. It could be great if the core server implementation could be agnostic to the framework used for the HTTP layer. 

After carefully reading the specifications, implement the core objects and proper interfaces, and the HTTP layer.

This is an MVP project. You have to stay focused on core features and do not add any extra features, helper method, logging stuff, etc.

Since it is an MVP, you have to write as less code as possible, focusing on simplicity and clarity.

## Structure of the project

All the code will be in the `src` folder. The `tests` folder will contain the unit tests.

Each part of the system will be in its own namespace and directory with `NeuronCore\A2A` the root namespace of the project.

All components must implement the related interface so we can create different implementations of each part of the server.

Forget about unit tests for now, we will add them later.