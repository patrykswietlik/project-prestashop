import requests
import os
from dotenv import load_dotenv
from xml.etree.ElementTree import fromstring, ParseError
import json
from jinja2 import Template
from requests_toolbelt.multipart.encoder import MultipartEncoder

def find_category_name(slug):
    with open('./categories.json', 'r', encoding='utf-8') as file:
        category_data = json.load(file)

        for category_element in category_data:
            names = category_element.get("name", [])
            urls = category_element.get("urls", [])

            for i in range(len(urls)):
                url = urls[i]
                extracted_url = url.split('/')[-2]

                if extracted_url == slug:
                    return names[i]

    return slug

def create_category(name, id_parent = 2):
    with open('./xml/category.xml', 'r', encoding='utf-8') as schema_file:
        schema_template = schema_file.read()

        variables = {
            "name": find_category_name(name),
            "link_rewrite": name,
            "description": name,
            "id_parent": id_parent 
        }

        template = Template(schema_template)
        category_xml = template.render(variables)

        response = session.post(
            f'{os.getenv("WEB_SERVICE_URL")}/categories',
            data=category_xml.encode('utf-8')
        )

        if response.status_code == 200 or response.status_code == 201:
            print(f'Category {name} created with id_parent: {id_parent}')

        # print(f"Category - Status Code: {response.status_code}")
        # print("Response Body:")
        # print(response.text)

        return response
    
def get_stock_available(product_id):
    response = session.get(
        f'{os.getenv("WEB_SERVICE_URL")}/stock_availables',
        params={
            'filter[id_product]': product_id,
            'display': 'full'
        }
    )

    print(f"Stock Available - Status Code: {response.status_code}")
    print("Response Body:")
    print(response.text)

    if response.status_code == 200:
        try:
            root = fromstring(response.text)
            stock_ids = root.findall('.//stock_available/id')
            products_attrs = root.findall('.//stock_available/id_product_attribute')
            response = []
            for i in range(len(stock_ids)):
                response.append((stock_ids[i].text, products_attrs[i].text))
            return response
        except ParseError as e:
            print("Failed to parse XML response.")
            print(f"ParseError: {e}")
            print("Response Text:", response.text)
    return None

def get_product_description(product_reference):
    with open("./productDetails.json", "r", encoding="utf-8") as file:
        products_details = json.load(file)

        for product in products_details:
            product_url = product[0]
            product_descriptions = product[-2]

            ref_from_json = product_url.split("/")[-2]

            if ref_from_json != product_reference:
                continue

            return product_descriptions[0]
        
    return product_reference
    

def update_stock_available(stock_id, quantity, id_product, ):
    with open('./xml/stock.xml', 'r', encoding='utf-8') as schema_file:
        schema_template = schema_file.read()

    variables = {
        "stock_id": stock_id,
        "quantity": quantity,
        "id_product": id_product
    }

    template = Template(schema_template)
    stock_xml = template.render(variables)


    try:
        fromstring(stock_xml)  
    except ParseError as e:
        print("Generated XML is invalid:")
        print(stock_xml)
        print(f"ParseError: {e}")
        return False

    response = session.put(
        f'{os.getenv("WEB_SERVICE_URL")}/stock_availables/{stock_id}',
        data=stock_xml.encode('utf-8')
    )

    print(f"Update Stock - Status Code: {response.status_code}")
    print("Response Body:")
    print(response.text)

    return response.status_code == 200

def update_single_stock(id_stock, id_product, id_product_attribute):
    with open('./xml/single-stock.xml', 'r', encoding='utf-8') as schema_file:
        schema_template = schema_file.read()

    variables = {
        "id_stock_available": id_stock,
        "id_product": id_product,
        "id_product_attribute": id_product_attribute
    }

    template = Template(schema_template)
    stock_xml = template.render(variables)


    try:
        fromstring(stock_xml)  
    except ParseError as e:
        print("Generated XML is invalid:")
        print(stock_xml)
        print(f"ParseError: {e}")
        return False

    response = session.put(
        f'{os.getenv("WEB_SERVICE_URL")}/stock_availables/{id_stock}',
        data=stock_xml.encode('utf-8')
    )

    print(f'Trying to update stock id {id_stock} for product {id_product}')

    return response.status_code == 200

def create_product(product_url, product_name, rating, original_price, discounted_price, categories, product_type):
    with open('./xml/product.xml', 'r', encoding='utf-8') as schema_file:
        schema_template = schema_file.read()

        product_url = product_url.split('/')[-2]

        if len(product_url) > 63:
            product_url = product_url[:63]

        original_price = original_price.replace('$', '')
        original_price = original_price.replace(",", "")

        if discounted_price:
            discounted_price = discounted_price.replace('$', '')
            discounted_price = discounted_price.replace(",", "")
        else:
            discounted_price = 0

        categories_data = '<category><id><![CDATA[2]]></id></category>'

        for category in categories:
            categories_data += f'<category><id><![CDATA[{category[1]}]]></id></category>'

        default_category_id = 2

        if len(categories) > 0:
            default_category_id = categories[-1][1]

        variables = {
            'id_manufacturer': 1,
            'id_supplier': 1,
            'id_category_default': default_category_id,
            'reference': product_url,
            'supplier_reference': 1,
            'price': original_price,
            'meta_description': get_product_description(product_url),
            'meta_keywords': f"{product_name}, product",
            'meta_title': product_name,
            'link_rewrite': product_url,
            'name': product_name,
            'description': get_product_description(product_url),
            'description_short': get_product_description(product_url),
            'categories': categories_data,
            'discounted_price': discounted_price,
            'product_type': product_type
        }

        template = Template(schema_template)
        product_xml = template.render(variables)

        response = session.post(
            f'{os.getenv("WEB_SERVICE_URL")}/products',
            data=product_xml.encode('utf-8')
        )

        print(response.text)

        if response.status_code == 201:
            try:
                root = fromstring(response.text)
                product_id = root.find('.//id').text
                print(f'Product created with ID - {product_id}')
                return product_id, product_url
            except ParseError:
                print("Failed to parse XML response.")
                print("Response Text:", response.text)
        return None

def upload_image_for_product(product_id, product_reference):
    with open("./productDetails.json", "r", encoding="utf-8") as file:
        products_details = json.load(file)

    img_folder = "./img"

    for product in products_details:
        product_url = product[0]
        product_images = product[-1]

        ref_from_json = product_url.split("/")[-2]

        if ref_from_json != product_reference:
            continue

        for image_file in product_images:
            image_path = os.path.join(img_folder, image_file)
            
            if not os.path.exists(image_path):
                print(f"Zdjęcie {image_path} nie istnieje. Pomijam.")
                continue

            try:
                with open(image_path, "rb") as img_file:
                    multipart_data = MultipartEncoder(
                        fields={
                            'image': (image_file, img_file, 'image/jpeg')
                        }
                    )
                    headers = {
                        'Content-Type': multipart_data.content_type
                    }
                    response = session.post(
                        f"{os.getenv('WEB_SERVICE_URL')}/images/products/{product_id}",
                        data=multipart_data,
                        headers=headers
                    )

                if response.status_code == 200 or response.status_code == 201:
                    print(f'Image uploaded for product created {product_id}')

            except Exception as e:
                print(f"Błąd podczas przesyłania obrazu {image_file}: {e}")

def create_product_option(name):
    with open('./xml/product_option.xml', 'r', encoding='utf-8') as schema_file:
        schema_template = schema_file.read()

    variables = {
        "name": name
    }

    template = Template(schema_template)
    product_option_xml = template.render(variables)

    response = session.post(
        f'{os.getenv("WEB_SERVICE_URL")}/product_options',
        data=product_option_xml.encode('utf-8')
    )

    if response.status_code == 201:
        try:
            root = fromstring(response.text)
            option_id = root.find('.//id').text
            print(f'Product option created with ID - {option_id}')
            return option_id
        except ParseError:
            print("Failed to parse XML response.")
            print("Response Text:", response.text)
    return None

def create_product_option_value(id_product_option, value):
    with open('./xml/product_option_value.xml', 'r', encoding='utf-8') as schema_file:
        schema_template = schema_file.read()

    variables = {
        "id_product_option": id_product_option,
        "value": value
    }

    template = Template(schema_template)
    product_option_value_xml = template.render(variables)

    response = session.post(
        f'{os.getenv("WEB_SERVICE_URL")}/product_option_values',
        data=product_option_value_xml.encode('utf-8')
    )

    if response.status_code == 201:
        try:
            root = fromstring(response.text)
            value_id = root.find('.//id').text
            print(f'Product option value created with ID - {value_id}')
            return value_id
        except ParseError:
            print("Failed to parse XML response.")
            print("Response Text:", response.text)
    return None

def create_combination(product_id, option_value_id, reference, price):
    with open('./xml/combination.xml', 'r', encoding='utf-8') as schema_file:
        schema_template = schema_file.read()

    variables = {
        "product_combination_id": product_id,
        "reference": reference,
        "price": price.replace('$', ''),
        "option_value_id": option_value_id
    }

    template = Template(schema_template)
    combination_xml = template.render(variables)

    response = session.post(
        f'{os.getenv("WEB_SERVICE_URL")}/combinations',
        data=combination_xml.encode('utf-8')
    )

    print(f"Combination - Status Code: {response.status_code}")
    print("Response Body:")
    print(response.text)

    if response.status_code == 201:
        try:
            root = fromstring(response.text)
            combination_id = root.find('.//id').text
            print(f'Combination created with ID - {combination_id}')
            return combination_id
        except ParseError:
            print("Failed to parse XML response.")
            print("Response Text:", response.text)
    return None

def load_products():
    with open('./productsGrid.json', 'r', encoding='utf-8') as file:
        products_data = json.load(file)
        created_categories = {}
        product_category = ""

        for category in products_data:
            category_url, *products = category

            category_elements = list(filter(lambda category: (category != '' and category not in ('product-category', 'marvelofficial.com', 'https:')), category_url.split('/')))

            for index, category_element in enumerate(category_elements):
                if category_element not in created_categories:
                    if index == 0:
                        category_response = create_category(category_url.split('/')[-2])
                    else:
                        category_response = create_category(category_url.split('/')[-2], created_categories[category_elements[index - 1]])
                    try:
                        root = fromstring(category_response.text)
                        category_id = root.find('.//id').text
                        created_categories[category_element] = category_id
                        product_category = category_element
                    except ParseError:
                        print("Failed to parse XML response.")
                        print("Response Text:", category_response.text)
                        continue

            for product in products:
                product_url, product_name, rating, original_price, discounted_price = product

                if product_category == 'marvel-hoodies' or product_category == 'marvel-tshirts':
                    options = {
                        "name": "Rozmiar",
                        "values": [
                            {"id": 1, "name": "XS", "quantity": 10},
                            {"id": 2, "name": "S", "quantity": 15},
                            {"id": 3, "name": "M", "quantity": 20},
                            {"id": 4, "name": "L", "quantity": 25}
                        ]
                    }       

                elif product_category == 'marvel-superhero-action-figures-collectible':
                    options = {
                        "name": "Wielkość",
                        "values": [
                            {"id": 1, "name": "Duża", "quantity": 10},
                            {"id": 2, "name": "Mała", "quantity": 15},
                        ]
                    }

                else:
                    options = None   

                if len(category_elements) == 0:
                    category_forwars = [category_elements]
                else:
                    category_forwars = [(name, created_categories[name]) for name in category_elements]

                if options:
                    product_type = 'combinations'
                else:
                    product_type = 'standard'

                product_id, product_ref = create_product(product_url, product_name, rating, original_price, discounted_price, category_forwars, product_type)

                if product_id and options:
                    option_name = options["name"]
                    option_id = create_product_option(option_name)
                    if option_id:
                        for value in options["values"]:
                            value_id = create_product_option_value(option_id, value["name"])
                            if value_id:
                                create_combination(product_id, value_id, product_ref, original_price)

                stock_ids = get_stock_available(product_id)

                if len(stock_ids) != 0:
                    for stock_id in stock_ids:
                        updated = update_single_stock(stock_id[0], product_id, stock_id[1])
                        if updated:
                            print(f"Stock updated successfully for Product ID {product_id}")
                        else:
                            print(f"Failed to update stock for Product ID {product_id}")
                
                upload_image_for_product(product_id, product_ref)
                input()

load_dotenv()

session = requests.Session()
session.headers.update({'Content-Type': 'application/xml'})
session.verify = False
session.auth = requests.auth.HTTPBasicAuth(os.getenv('WEB_SERVICE_KEY'), '')

load_products()