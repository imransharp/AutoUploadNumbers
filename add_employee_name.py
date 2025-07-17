# add employee name with department and employee number.

import mysql.connector
import pandas as pd

# Connect to the MySQL database
def connect_to_db():
    connection = mysql.connector.connect(
       
    )
    return connection

# Check if number exists in tbl_whitelist
def check_if_number_exists(connection, number_with_prefix):
    cursor = connection.cursor()
    query = "SELECT COUNT(*) FROM table WHERE col = %s"
    cursor.execute(query, (number_with_prefix,))
    result = cursor.fetchone()[0]  
    return result > 0 

# Fetch ntn and client_name from clients table
def get_ntn_and_client_name(connection, client_search_name):
    cursor = connection.cursor()
    query = "SELECT col1, col1 FROM table2 WHERE col1 LIKE %s and col1 = 'Active'"
    cursor.execute(query, (f"%{client_search_name}%",))  
    result = cursor.fetchone() 
    if result:
        return result
    else:
        print(f"No matching client found for '{client_search_name}'")
        return None, None

# Check if employee already exists based on msisdn
def check_if_employee_exists(connection, msisdn):
    cursor = connection.cursor()
    query = "SELECT COUNT(*) FROM table1 WHERE col1 = %s"
    cursor.execute(query, (msisdn,))
    result = cursor.fetchone()[0] 
    return result > 0 

# Get client_id from the clients table based on client_name
def get_client_id(connection, client_name):
    cursor = connection.cursor()
    query = "SELECT col1 FROM table3 WHERE col1 LIKE %s and col2 = 'Active'"
    cursor.execute(query, (f"%{client_name}%",))
    result = cursor.fetchone()  
    if result:
        return result[0]  
    else:
        print(f"No matching client found for '{client_name}'")
        return None


# Main process to read and insert numbers and employee names
def process_numbers():
    connection = connect_to_db()

    # Read the Excel file from D drive
    file_path = 'D:\\folderName\\fileName.xlsx'
    df = read_excel_file(file_path)

    # Get client name from console input
    client_search_name = input("Enter the client name to search (e.g., 'Road Group'): ")

    while True:
        # Fetch client_id for the provided client name
        client_id = get_client_id(connection, client_search_name)

        if client_id:
            # Fetch the 'created_by' field using the retrieved client_id
            query = f";"
            cursor = connection.cursor()
            cursor.execute(query)
            result = cursor.fetchone()

            if result:
                created_by = result[0]
                print(f"Created By: {created_by}")

                # Get department input from the user
                department_name = input("Enter the department name (e.g., 'Department 1'): ")

                # Fetch department details using created_by and department_name
                department_query = ";"
                cursor.execute(department_query, (created_by, f"%{department_name}%"))
                department_info = cursor.fetchone()

                if department_info:
                    print(f"Department Info: {department_info}")
                    department_id = department_info[0]

                    # Insert all the numbers and names from the Excel file into the employees table
                    for index, row in df.iterrows():
                        try:
                            number = row['Number']
                            name = row['Name']
                            # Ensure '92' is only added if not already present
                            if not str(number).startswith("92"):
                                number_with_prefix = f"92{number}"
                            else:
                                number_with_prefix = str(number)

                            # convert back to int
                            if number_with_prefix is not None:
                                try:
                                    int_number_with_prefix = int(number_with_prefix)
                                except ValueError:
                                    print("Invalid number format. Cannot convert to integer.")
                                    int_number_with_prefix = None  # Handle the error appropriately
                           
                            insert_employee_query = f"""
                               
                            """
                            
                            cursor.execute(insert_employee_query)
                            print(f"Added or updated employee: {name}, {int_number_with_prefix}")
                        except Exception as e:
                            print(f"Failed to insert or update number {int_number_with_prefix}: {e}")                       

                    # Commit the transaction after inserting all numbers and names
                    connection.commit()
                    print("All employees have been successfully added to the employees table.")
                else:
                    print(f"No department found with name '{department_name}' for created_by '{created_by}'.")
                break
            else:
                print("No employees found with the given client ID. Please enter another client name.")
                client_search_name = input("Enter another client name: ")
        else:
            print("Client not found. Please enter a valid client name.")
            client_search_name = input("Enter another client name: ")

    cursor.close()
    connection.close()

if __name__ == "__main__":
    process_numbers()
