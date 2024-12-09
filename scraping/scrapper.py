from bs4 import BeautifulSoup
from selenium import webdriver
import requests
import re
import os
import json

HEADERS = {'User-Agent': 'Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148'}
URL="https://marvelofficial.com/shop/?fbclid=IwZXh0bgNhZW0CMTAAAR3OzSpffy0uw1Yeq72BBzE1fOb_DCkAK2pFNSAJI2WhkjbZZELRfwS_gpA_aem_i6ncYj8tTY9jABG0vPnrxw"
urls=[]
products_grid=[]
product_details=[]
product_visited=[]

def adressesOfSubclasses(urls):
    page = requests.get(URL, headers=HEADERS)
    soup = BeautifulSoup(page.text, 'html.parser')
    category_section = soup.find('div', id='wide-nav')
    
    if category_section:
        links = category_section.find_all('li')
        for item in links:
            a_tag = item.find('a', class_='nav-top-link')
            if a_tag:
                category = {
                    "name": [a_tag.get_text(strip=True)],
                    "urls": [a_tag['href']]
                }
                sub_menu = item.find('ul', class_='sub-menu nav-dropdown nav-dropdown-simple')
                if sub_menu:
                    for sub_item in sub_menu.find_all('a'):
                        category['name'].append(sub_item.get_text(strip=True))
                        category["urls"].append(sub_item['href'])
                urls.append(category)
    else:
        print("empty")


def printUrls(urls):
    for category in urls:
        print(f"Category Name: {category['name'][0]}")
        print("Subcategories and URLs:")
        for i in range (1, len(category['urls'])):
            print(f"nazwa-{category['name'][i]} adres-{category['urls'][i]} ")
        print()


def gettingNamesofSubcattegories():
    page=requests.get(URL,headers=HEADERS)
    soup=BeautifulSoup(page.text,'html.parser')
    category_section = soup.find('div', id='wide-nav')
    print(category_section)
    if category_section:
        header=category_section.find_all('li')
        for item in header:
            a_tag = item.find('a',class_='nav-top-link')
            if a_tag:
                category_name = a_tag.get_text(strip=True)
                print("["+category_name+"]")
            else:
                a2_tag = item.find('a')
                if a2_tag:
                    category_name = a2_tag.get_text(strip=True)
                    print(category_name)
    else:
        print("empty")


def productsCategories(url):
    products=[]
    products.append(url)
    page=requests.get(url,headers=HEADERS)
    soup=BeautifulSoup(page.text,'html.parser')
    while True:
        category_section = soup.findAll('div', class_='product-small box')
        for item in category_section:
            infos=item.find('div',class_='box-text box-text-products text-center grid-style-2')
            if infos:
                text=infos.find('a',class_='woocommerce-LoopProduct-link woocommerce-loop-product__link')
                product_info = [text['href']] 
    
                if product_info[0] not in product_visited: #sprawdzamy czy dany obiekt juz scrappowany
                    product_visited.append(product_info[0])
                    product_details.append(productSite(product_info[0]))

                sub_item=text.get_text(strip=True)
                product_info.append(sub_item)
                rating=infos.find('div', class_='star-rating star-rating--inline')
                if rating:
                    sub_item=rating.get_text(strip=True)
                    product_info.append(sub_item)
                else:
                    sub_item=None
                    product_info.append(sub_item)
                prices=infos.findAll('span',class_='woocommerce-Price-amount amount')
                for price in prices:
                    product_info.append(price.get_text(strip=True))
                if len(prices)==1:
                    product_info.append(None)
                products.append(product_info)
        next_page=soup.find('a', class_='next page-number')
        if next_page:
            href = next_page['href']
            page=requests.get(href,headers=HEADERS)
            soup=BeautifulSoup(page.text,'html.parser')
        else:
            print("Link nie znaleziony.")
            break
    products_grid.append(products)

def saveImages(images,info):
    folder = "img"
    img_names=[]
    print(images)
    if not os.path.exists(folder):
        os.makedirs(folder)
    else:
        print(f"Folder '{folder}' already exists. Proceeding with downloads.")
    for idx, url in enumerate(images):
        try:
            response = requests.get(url, stream=True)
            if response.status_code == 200:
                file_name = os.path.basename(url)
                file_path = os.path.join(folder, file_name)
                with open(file_path, 'wb') as file:
                    file.write(response.content)
                img_names.append(file_name)
                print(f"Downloaded: {file_name}")
            else:
                print(f"Failed to download {url} - Status code: {response.status_code}")
        except Exception as e:
            print(f"Error downloading {url} - {e}")
    info.append(img_names)

def getFullSizeUrl(thumbnail_url):
    full_size_url = re.sub(r'-\d+x\d+(?=\.\w+$)', '', thumbnail_url)
    return full_size_url

def productSite(url):
    product=[]
    product.append(url)
    image_urls=[]
    page=requests.get(url,headers=HEADERS)
    soup=BeautifulSoup(page.text,'html.parser')
    driver = webdriver.Chrome()
    driver.get(url)
    driver.implicitly_wait(1)
    soup_images = BeautifulSoup(driver.page_source, 'html.parser')
    images = soup_images.find('div', class_='flickity-slider')
    if images:
        for img_tag in images.find_all('img'):
            src = img_tag.get('src')
            if src:
                image_urls.append(getFullSizeUrl(src))
    else:
        print(f"kurwa nie ma tych images")
    driver.quit()

    #krotkie info
    infos=soup.find('div',class_='product-short-description')
    if infos:
        delivery=infos.find('span')
        if delivery:
            full_name_tag=infos.find_all('p')[1]
            children = full_name_tag.find_all(recursive=False)  # Bezpośrednie dzieci
            if len(children) == 1 and children[0].name == 'span':
                full_name_tag=infos.find_all('p')[0]
                title = full_name_tag.get_text(strip=True)
                product.append(title)
            else:
                title = full_name_tag.get_text(strip=True)
                product.append(title)
            delivery=delivery.get_text(strip=True)
            product.append(delivery)
            properties=infos.find_all('li')
            properties_list=[]
            for property_m in properties:
                property=property_m.get_text(strip=True)
                properties_list.append(property)
            diffrent_properties=infos.find_all('p')
            for property_m in diffrent_properties[2:]:
                lines = property_m.find_all(text=True)
                result_list = [line.strip('– ').strip() for line in lines if line.strip()]
                properties_list.extend(lines)
            product.append(properties_list)
        else:
            full_name_tag=infos.find_all('p')[0]
            if full_name_tag:
                title = full_name_tag.get_text(strip=True)
                product.append(title)
            product.append(None)
            properties=infos.find_all('li')
            properties_list=[]
            for property_m in properties:
                property=property_m.get_text(strip=True)
                properties_list.append(property)
            diffrent_properties=infos.find_all('p')
            for property_m in diffrent_properties[1:]:
                lines = property_m.find_all(text=True)
                result_list = [line.strip('– ').strip() for line in lines if line.strip()]
                properties_list.extend(result_list)
            product.append(properties_list)

        stock=soup.find('p',class_="stock in-stock")
        if stock:
            stock=stock.get_text(strip=True)
            product.append(stock)
        else:
            product.append(None)
    print(product)
    #opis produktu
    description_div=soup.find('div',class_="woocommerce-Tabs-panel woocommerce-Tabs-panel--description panel entry-content active")
    content = []
    if description_div:
        for element in description_div.find_all(recursive=False):
            if element.name in ['p', 'h2', 'ul', 'li']:
                if element.name in ['p', 'h2']:
                    text = element.decode_contents()
                    text = text.replace('<b>', '').replace('</b>', '')
                    quick_format = BeautifulSoup(text, 'html.parser')
                    content.append(quick_format.get_text(strip=True))
                elif element.name == 'ul':
                    for li in element.find_all('li'):
                        content.append(f"- {li.get_text(strip=True)}")
            elif element.name in ['b', 'a']:
                content.append(f" {element.get_text(strip=True)}")
    else:
        print("Description div not found.")
    for line in content:
        print(line)
    product.append(content)
    img_names=[]
    for url in image_urls:
        file_name = os.path.basename(url)
        img_names.append(file_name)
    product.append(img_names)
    saveImages(image_urls,product)
    return product
  
def save_to_json(data, filename):
    with open(filename, 'w', encoding='utf-8') as json_file:
        json.dump(data, json_file, indent=4, ensure_ascii=False) 
    print(f"Dane zapisano do pliku {filename}")


adressesOfSubclasses(urls)
for url in urls:
        productsCategories(url['urls'][0])
        if len(url['urls']) > 1:
            for sub_link in url['urls'][1:]:
                productsCategories(sub_link)
save_to_json(urls, "categories.json")
save_to_json(products_grid, "productsGrid.json")
save_to_json(product_details,"productDetails.json")