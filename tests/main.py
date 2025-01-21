import os
import sys
import warnings

import urllib3
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import random
from selenium.webdriver.chrome.options import Options
import requests
from xml.etree import ElementTree as ET


'''
a. Dodanie do koszyka 10 produktów (w różnych ilościach) z dwóch różnych
kategorii,
b. Wyszukanie produktu po nazwie i dodanie do koszyka losowego produktu
spośród znalezionych
c. Usunięcie z koszyka 3 produktów,
d. Rejestrację nowego konta,
e. Wykonanie zamówienia zawartości koszyka,
f. Wybór metody płatności: przy odbiorze,
g. Wybór jednego z dwóch przewoźników,
h. Zatwierdzenie zamówienia,
i. Sprawdzenie statusu zamówienia.
j. Pobranie faktury VAT.
'''

#admin credentials
login = 'patryk.swietlik.off@gmail.com'
password = 'patryk.swietlik.off@gmail.com'

SHOP_URL='https://localhost:18977/'
API_KEY = 

warnings.filterwarnings("ignore", message="Unverified HTTPS request")
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

XML_TEMPLATE = """<?xml version="1.0" encoding="UTF-8"?>
<prestashop xmlns:xlink="http://www.w3.org/1999/xlink">
    <order_history>
        <id />
        <id_employee>1</id_employee>
        <id_order_state>5</id_order_state>
        <id_order>{order_id}</id_order>
        <date_add />
    </order_history>
</prestashop>
"""

session = requests.Session()
session.verify = False
session.auth = requests.auth.HTTPBasicAuth(API_KEY, "")

print(f"Using shop URL: {SHOP_URL}")

def add_10_products_from_2_categories(driver):
    categories = [f'{SHOP_URL}index.php?id_category=53&controller=category', f'{SHOP_URL}index.php?id_category=48&controller=category']
    productsToAdd = 10

    for j in range(len(categories)):
        driver.get(categories[j])
        WebDriverWait(driver, 5).until(
            EC.visibility_of_all_elements_located((By.CLASS_NAME, 'thumbnail'))
        )

        products = driver.find_elements(By.CLASS_NAME, 'thumbnail')

        for i in range(len(products)):
            if (productsToAdd == 1 and j == 0) or productsToAdd == 0:
                break
            product = products[i]
            product.click()
            amountField = WebDriverWait(driver, 5).until(
                EC.visibility_of_element_located((By.NAME, 'qty'))
            )
            #czyszczenie input fielda
            driver.execute_script("arguments[0].value = '';", amountField)
            amountField.send_keys(random.randint(1,4))
            addButton = driver.find_element(By.CLASS_NAME, 'add-to-cart')
            if addButton.get_attribute('disabled') in ['true', 'disabled']:
                driver.get(categories[j])
                products = driver.find_elements(By.CLASS_NAME, 'thumbnail')
                continue
            try:
                addButton.click()
            except Exception as e:
                driver.get(categories[j])
                products = driver.find_elements(By.CLASS_NAME, 'thumbnail')
                continue
            WebDriverWait(driver, 5).until(
                EC.visibility_of_element_located((By.CSS_SELECTOR, '.cart-content'))
            )
            driver.get(categories[j])
            WebDriverWait(driver, 5).until(
                EC.visibility_of_all_elements_located((By.CLASS_NAME, 'thumbnail'))
            )
            products = driver.find_elements(By.CLASS_NAME, 'thumbnail')
            productsToAdd -= 1
    print("Test 1 done")


def search_and_add_by_name(driver):
    name = 'Marvel Deadpool Mask'
    searchInput = driver.find_element(By.NAME, 's')
    searchInput.send_keys(name)
    searchInput.submit()

    products = driver.find_elements(By.CLASS_NAME, 'thumbnail')
    productId = random.randint(0, len(products) - 1)
    products[productId].click()
    addButton = driver.find_element(By.CLASS_NAME, 'add-to-cart')
    if addButton.get_attribute('disabled'):
        return
    addButton.click()
    WebDriverWait(driver, 5).until(
        EC.visibility_of_element_located((By.CSS_SELECTOR, '.cart-content'))
    )
    print("Test 2 done")


def delete_3_products(driver):
    cart = f'{SHOP_URL}index.php?controller=cart&action=show'
    driver.get(cart)
    for _ in range(3):
        WebDriverWait(driver, 10).until(
            EC.visibility_of_all_elements_located((By.CLASS_NAME, 'cart-item'))
        )
        products = driver.find_elements(By.CLASS_NAME, 'cart-item')
        if len(products) == 0:
            print(f'Test 3 Empty cart on iteration {_}')
            return
        product = products[0]
        product.find_element(By.CLASS_NAME, 'remove-from-cart').click()
        WebDriverWait(driver, 5).until(
            EC.staleness_of(product)
        )
    print("Test 3 done")


def register_account(driver):
    registration = f'{SHOP_URL}index.php?controller=authentication&create_account=1'
    firstName = 'Jan'
    lastName = 'Kowalskowy'
    email = 'aaaa.bbbb' + str(random.randint(0, 999999)) + '@wp.pl'
    password = 'qwepoi'
    driver.get(registration)

    driver.find_element(By.NAME, 'firstname').send_keys(firstName)
    driver.find_element(By.NAME, 'lastname').send_keys(lastName)
    driver.find_element(By.NAME, 'email').send_keys(email)
    driver.find_element(By.NAME, 'password').send_keys(password)
    driver.find_element(By.NAME, 'customer_privacy').click()
    driver.find_element(By.NAME, 'psgdpr').click()
    driver.find_element(By.CLASS_NAME, 'form-control-submit').click()
    print("Test 4 done")


def submit_order(driver):
    order = f'{SHOP_URL}index.php?controller=order'
    city = 'Miasto'
    address = 'Uliczna'
    postCode = '12-345'
    firstName = 'Jan'
    lastName = 'Kowalskowy'
    email = 'aaaa.bbbb' + str(random.randint(0, 999999)) + '@wp.pl'
    driver.get(order)
    WebDriverWait(driver, 5).until(
        EC.presence_of_element_located((By.CLASS_NAME, '-unreachable'))
    )
    u = driver.find_elements(By.CLASS_NAME, '-unreachable')
    if len(u) == 3:
        driver.find_element(By.NAME, 'firstname').send_keys(firstName)
        driver.find_element(By.NAME, 'lastname').send_keys(lastName)
        driver.find_element(By.NAME, 'email').send_keys(email)
        driver.find_element(By.NAME, 'customer_privacy').click()
        driver.find_element(By.NAME, 'psgdpr').click()
        driver.find_element(By.NAME, 'continue').click()

    driver.find_element(By.NAME, 'address1').send_keys(address)
    driver.find_element(By.NAME, 'postcode').send_keys(postCode)
    driver.find_element(By.NAME, 'city').send_keys(city)
    driver.find_element(By.NAME, 'confirm-addresses').click()
    driver.find_element(By.NAME, 'confirmDeliveryOption').click()
    driver.find_element(By.ID, 'payment-option-2').click()
    driver.find_element(By.ID, 'conditions_to_approve[terms-and-conditions]').click()
    driver.find_element(By.ID, 'payment-confirmation').find_element(By.CLASS_NAME, 'btn').click()
    print("Test 5 done")


def check_order_status(driver):
    driver.get(f'{SHOP_URL}index.php?controller=history')
    orders = driver.find_elements(By.CLASS_NAME, 'order-actions')
    orders[0].find_element(By.TAG_NAME, 'a').click()
    print("Test 6 done")


def get_latest_order_id():
    response = session.get(f"{SHOP_URL}api/orders?sort=[id_DESC]&limit=1")
    if not response.ok:
        raise Exception(f"Failed to fetch latest order: {response.text}")

    root = ET.fromstring(response.text)
    order = root.find("orders/order")
    if order is None:
        raise Exception("No orders found.")

    return int(order.attrib["id"])

def update_order_state(order_id):
    xml_data = XML_TEMPLATE.format(order_id=order_id)
    response = session.post(f"{SHOP_URL}api/order_histories", data=xml_data)

def mark_last_order_as_delivered():
    try:
        latest_order_id = get_latest_order_id()
        update_order_state(latest_order_id)
    except Exception as e:
        pass

def vat_invoice(driver):
    mark_last_order_as_delivered()
    driver.get(f'{SHOP_URL}index.php?controller=history')

    table = driver.find_element(By.CSS_SELECTOR,
                                'table.table.table-striped.table-bordered.table-labeled.hidden-sm-down')
    rows = table.find_elements(By.TAG_NAME, 'tr')

    if len(rows) > 1:
        first_order = rows[1]

        invoice_link = first_order.find_element(By.CSS_SELECTOR, 'td.text-sm-center.hidden-md-down a')

        invoice_link.click()
    else:
        print("Brak zamówień na liście.")
    print("Test 7 done")

options = Options()
options.add_argument('--ignore-certificate-errors')
options.add_argument('--allow-insecure-localhost')
options.add_argument('--disable-web-security')

driver = webdriver.Chrome(options=options)
driver.maximize_window()
driver.get(SHOP_URL)


add_10_products_from_2_categories(driver)
search_and_add_by_name(driver)
delete_3_products(driver)
register_account(driver)
submit_order(driver)
check_order_status(driver)
vat_invoice(driver)
