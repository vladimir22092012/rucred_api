try:
    from fpdf import FPDF
    from datetime import date
    import PyPDF2
    import sys
    import os

    laravel_dir = '/home/rucred-crm/laravel-api/laravelapi/'
    print('start script')
    #Получаем аргументы при запуске скрипта 1 - ИД пользователя 2- ФИО 3 - АСП КОД 4 - Файл в к
    print('get arrgs')
    user_id = sys.argv[1]
    fioArg = sys.argv[2]
    asp = sys.argv[3]
    file = sys.argv[4]

    fioarr = fioArg.split('-')

    date = date.today()
    asp_file_name = laravel_dir + 'public/asp_'+str(user_id)+'.pdf'


    print('init fpdf')
    #Создаём страницу с подписью
    pdf = FPDF()
    #Добавляем шрифт с кирилицей
    pdf.add_page()
    pdf.add_font('DejaVu', '', laravel_dir + 'fonts/ttf/DejaVuSansCondensed.ttf', uni=True)
    pdf.set_font('DejaVu', '', 10)

    #Добавляем подпись в документ
    pdf.set_xy(140, 250)
    pdf.write(0, 'Подпись документа')
    pdf.set_xy(140, 255)
    pdf.write(0, 'Подписант: ' + ' '.join(fioarr))
    pdf.set_xy(140, 260)
    pdf.write(0, 'Дата подписания: ' + str(date))
    pdf.set_xy(140, 265)
    pdf.write(0, 'Код подтверждения: ' + str(asp))
    pdf.set_xy(140, 270)
    pdf.write(0, 'Система ЭДО: Рестарт.Онлайн')

    #Сохраняем временный файл с подписью
    pdf.output(asp_file_name)
    writer = PyPDF2.PdfWriter()
    #Пишем подпись в документ открываем файл который подписываем
    with open(file, 'rb') as file_input:
        pdf = PyPDF2.PdfReader(file_input)

        #Открываем файл с подписью
        with open(asp_file_name, 'rb') as file_wotermark:
            wotermark = PyPDF2.PdfReader(file_wotermark)

            #Получаем все страницы с подписью
            for page in pdf.pages:
                #Получили подпись
                first_wotermark = wotermark.pages[0]

                #Мёрджим страницы
                page.merge_page(first_wotermark)
                writer.add_page(page)

            #Сохраняем подписываемый файл
            with open(file, 'wb') as output:
                writer.write(output)

    #Удаляем временный файл с подписью
    os.remove(asp_file_name)
    print('asp complete')
except Exception as e:
    print(e)
