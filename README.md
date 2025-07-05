# DonateX

**DonateX** is a community-powered donation and volunteering platform. It allows users to donate items (food, books, clothes, etc.), volunteer for social causes, and raise/help blood donation requests.

## 🌟 Features

- **User Roles**: Volunteer, Donor, Blood Donor, Help Seeker
- **Dashboard**: Role-based navigation and notification center
- **Donations**: Post items, view, and claim donations
- **Blood Requests**: Request and respond to blood needs
- **Volunteering**: Post and participate in social activities
- **Reports**: View personal contribution statistics

## 🧰 Tech Stack

- **Frontend**: HTML, CSS (Bootstrap), JS
- **Backend**: PHP (Core PHP)
- **Database**: MySQL
- **Platform**: XAMPP (Apache, MySQL)

## 🚀 Installation (Using XAMPP + Git)

1. **Clone the repository**
    ```bash
    git clone https://github.com/dnaveenshankar/DonateX.git
    ```

2. **Move project folder to XAMPP's htdocs directory**
    ```bash
    mv DonateX /xampp/htdocs/
    ```

3. **Import the database**
    - Open phpMyAdmin: `http://localhost/phpmyadmin`
    - Create a new database named `donatex`
    - Import the `.sql` file (you can export from the live DB)

4. **Configure database in files**
    - Check the connection settings inside PHP files (host, username, password, database)
    - Usually located at the top of each file

5. **Run the project**
    - Open browser and visit: `http://localhost/DonateX/`

## 📄 License

This project is developed under a **Free and Open License**. You are free to use, modify, and distribute it for non-commercial and educational purposes.

## 📬 Contact Developer

For support, suggestions, or collaborations:  
**<a href="https://naveenshankar.in">Contact the developer</a>**