# WHMCS MeshCentral Connector Module

This is a WHMCS provisioning module that acts as a bridge between your WHMCS billing system and your MeshCentral remote management server. It allows you to sell remote management as a product, automatically creating dedicated device groups and users for your clients.

**Current Status:** This is a functional blueprint and should be reviewed, tested, and secured by a qualified developer before being used in a live production environment.

---

## Features

-   **Automated Provisioning**: Automatically creates a new MeshCentral Device Group when a client purchases the associated product.
-   **User Creation**: Creates a corresponding user in MeshCentral for the WHMCS client, with permissions scoped only to their device group.
-   **Single Sign-On (SSO)**: Clients can access their MeshCentral control panel directly from the WHMCS client area without needing a separate password.
-   **Client Area Integration**:
    -   Displays a direct SSO link to the MeshCentral dashboard.
    -   Provides a unique agent installer link for the client's device group.
    -   Lists all connected devices and their online/offline status.
-   **Automated Termination**: Deletes the MeshCentral user and device group when the WHMCS service is terminated.

---

## Installation

1.  Clone or download this repository.
2.  Navigate to your WHMCS installation directory.
3.  Upload the entire `meshcentral` folder to `/modules/servers/`.

The final file structure should look like this:
/whmcs/
└── modules/
└── servers/
└── meshcentral/
├── .gitignore
├── meshcentral.php
├── lib/
│   └── MCH_API.php
└── templates/
└── clientareadetails.tpl

---

## Configuration

1.  **Set up the Server in WHMCS**:
    -   In your WHMCS Admin Area, go to **Setup > Products/Services > Servers**.
    -   Click **Add New Server**.
    -   **Name**: Give it a recognizable name (e.g., "My MeshCentral Server").
    -   **Hostname or IP Address**: Enter your MeshCentral server URL (e.g., `mon.monster-it.co.uk`).
    -   Under "Server Details", change the **Type** to **MeshCentral Connector**.
    -   Leave Username and Password blank. They are not used. The API Key is configured at the product level.
    -   Click **Save Changes**.

2.  **Create a Product in WHMCS**:
    -   Go to **Setup > Products/Services > Products/Services**.
    -   Click **Create a New Product**.
    -   Fill in the product details as required.
    -   Go to the **Module Settings** tab.
    -   For **Module Name**, select **MeshCentral Connector**.
    -   For **Server Group**, select the group containing the server you just created.
    -   You will see two options appear:
        -   **ServerURL**: Enter your full MeshCentral URL (e.g., `https://mon.monster-it.co.uk`).
        -   **APIKey**: Enter an API key generated from your MeshCentral account (`My Account` -> `API Keys`). Use a **Login Key** type.
    -   Click **Save Changes**.

Your product is now ready. When a client orders it, a new device group will be provisioned in MeshCentral.

---

## Security Warning

-   This code is provided as a proof-of-concept. It lacks robust error handling, logging, and advanced security hardening.
-   The MeshCentral API key has significant privileges. Ensure your WHMCS installation and database are secure.
-   Always sanitize inputs and validate API responses in a production version.
-   It is highly recommended to have this code reviewed by an experienced developer.
