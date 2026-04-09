# main.py

def greet(name):
    return f"Hello, {name}! Welcome to Python."

def add_numbers(a, b):
    return a + b

def main():
    print("=== Python Test Program ===")

    name = input("Enter your name: ")
    print(greet(name))

    num1 = int(input("Enter first number: "))
    num2 = int(input("Enter second number: "))

    result = add_numbers(num1, num2)
    print(f"Sum of {num1} and {num2} is: {result}")

if __name__ == "__main__":
    main()